<?php
class Seth_DI_Container extends Seth_DI_Dice implements DI_Container {
	
	protected $factory = NULL;
	
	/**
	 * Array of interceptors, key is the targeting annotation, value the array of interceptor names
	 * @var Array(String=>Array(String))
	 */
	protected $interceptors = array();
	
	/**
	 * Creates a new DI container using the given annotation factory to read annotations from classes and their properties and methods.
	 * 
	 * @param Annotation_Factory $factory
	 */
	public function __construct(Annotation_Factory $factory) {
		$this->factory = $factory;
		
		// register own class' and annotation factorys annotations
		$this->register(__CLASS__);
		$this->register(get_class($factory));
		
		// add own instance for singleton's instance
		$this->instances[__CLASS__] = $this;
		
		// add annotation factory's instance if it is a singleton
		if ( $factory->has_annotation($factory, Singleton::class) ) {
			$this->instances[get_class($factory)] = $factory;
		}
	}
	
	/**
	 * Return an array with all available singletons
	 * 
	 * @return Array(String=>Object)
	 */
	public function get_singletons() {
		return $this->instances;
	}
	
	/**
	 * Returns a fully constructed object based on $name using $args and $share as constructor arguments if supplied
	 * 
	 * @param
	 *        string name The name of the class to instantiate
	 * @param array $args
	 *        An array with any additional arguments to be passed into the constructor upon instantiation
	 * @param array $share
	 *        Whether or not this class instance be shared, so that the same instance is passed around each time
	 * @return object A fully constructed object based on the specified input arguments
	 */
	public function create($name, array $args = [], array $share = []) {
		
		$rule = $this->getRule($name);

		
		
		if (isset($rule['substitutions']) && array_key_exists($name, $rule['substitutions']) && array_key_exists('instance', $rule['substitutions'][$name])) {
			if (is_array($rule['substitutions'][$name]['instance'])) {
				$name = $rule['substitutions'][$name]['instance'][0];
			}
			else {
				$name = $rule['substitutions'][$name]['instance'];
			}
			$rule = $this->getRule($name);
		}
		
		// Is there a shared instance set? Return it. Better here than a closure for this, calling a closure is slower.
		if (isset($this->instances[$name]))
			return $this->instances[$name];
		
		$this->register(isset($rule['instanceOf']) ? $rule['instanceOf']:$name);
		
		
		$interceptors = $this->get_interceptors($name);
		
		
		if ( $interceptors === null ) {
			$obj = parent::create($name, $args, $share);
		} else {
			$factories = $this->get_factories($name, $interceptors);
			
			// add default factory
			$factories[] = array($this->create('DI_Factory'), 'factory');
			
			$ctx = new Factory_Context($factories);
			$ctx->clazz($name);
			$ctx->data(array(
				'interceptors' => $this->interceptors,
			));
			
			$obj = $ctx->proceed();
		}
		
		$this->injectFields($obj, $name, $rule);
		return $obj;
	}
	
	protected function get_interceptors($clazz) {
		$ref = new ReflectionClass($clazz);
		
		$interceptors = null;
		$annotations = $this->factory->get_annotations($ref);
		foreach ( $annotations as $name => $annotation ) {
			$annotation = ( is_array($annotation) ) ? current($annotation):$annotation;
			
			if ( $annotation instanceof Interceptable ) {
				if ( $interceptors === null ) {
					$interceptors = array();
				}
				if ( isset($this->interceptors[$name])) {
					$interceptors = array_merge($interceptors, $this->interceptors[$name]);
				}
			}
		}
		return $interceptors;
	}
	
	protected function get_factories($clazz, array $interceptors) {
		
		$factories = array();
			foreach ( $interceptors as $name ) {
				$interceptor = $this->create($name);
				
				$ref = new ReflectionClass($interceptor);
				foreach ( $ref->getMethods(ReflectionMethod::IS_PUBLIC) as $method ) {
					if ( $this->factory->has_annotation($method, Factory::class)) {
						$factories[] = array($interceptor, $method->getName());
						break;
					}
				}
			}
		return $factories;
	}
	
	/**
	 * Scans the given object and looks out for fields which do have the Inject annotation attached. All fields which needs to be injected will
	 * cause a new create() call to this container.
	 * 
	 * @param Object $obj
	 * @param String $name
	 * @param Array $rule
	 * @return void
	 */
	protected function injectFields($obj, $name, array $rule) {
		if ($obj !== null) {
			
			$clazz = new ReflectionClass(isset($rule['instanceOf']) ? $rule['instanceOf']:$name);
			foreach ( $this->factory->get_fields($clazz, Inject::class) as $prop_name => $annotations ) {
				$prop = $clazz->getProperty($prop_name);
				$prop->setAccessible(true);
				
				if ($prop->getValue($obj) === null) {
					$injectClazz = ( $annotations[Inject::class]->value() == '' && isset($annotations[Variable::class]) ) ? $annotations[Variable::class]->value():$annotations[Inject::class]->value();
					$prop->setValue($obj, $this->create($injectClazz));
				}
			}
		}
	}
	
	
	/**
	 * Registers given class name to the DI container. This method will not create a new instance of given class.
	 * 
	 * @param String $clazz
	 * @return void
	 */
	public function register($clazz) {
		$ref = new ReflectionClass($clazz);
		
		$annotations = $this->factory->get_annotations($ref);
		
		
		if ( $this->factory->has_annotation($ref, Singleton::class)) {
			$this->addRule($ref->getName(), array(
					'shared' => true 
			));
		}
		
		$existingRule = $this->getRule('*');
		foreach ($ref->getInterfaceNames() as $interface) {
			$rule = [
					'substitutions' => [
							$interface => [
									'instance' => $ref->getName() 
							] 
					] 
			];
			$this->addRule('*', $rule);
		}
		
		if ( isset($annotations[Interceptor::class]) ) {
			$intercept = ( is_array($annotations[Interceptor::class]) ? $annotations[Interceptor::class]:array($annotations[Interceptor::class]));
			
			foreach ( $intercept as $a ) {
				if ( !isset($this->interceptors[$a->value()])) {
					$this->interceptors[$a->value()] = array();
				}
				$this->interceptors[$a->value()][] = $ref->getName();
			}
		}
	}
}