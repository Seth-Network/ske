<?php
class Seth_DI_Factory {
	
	/**
	 * @Inject
	 * 
	 * @var Annotation_Factory
	 */
	protected $af;
	
	/**
	 *
	 * @param Factory_Context $ctx        
	 */
	public function factory(Factory_Context $ctx) {
		$clazz_name = $ctx->clazz();
		$ref = new ReflectionClass($clazz_name);
		
		$proxy = "Seth_Generated_" . $clazz_name;
		
		// class not loaded yet: see if interface_clazz's file is newer than generated annotation's clazz file
		$clazz_file = Kohana::find_file('classes', str_replace('_', '/', $clazz_name));
		$proxy_file = Kohana::find_file('classes', str_replace('_', '/', $proxy));
		
		if ($proxy_file === false || filemtime($clazz_file) > filemtime($proxy_file)) {
			
			$data = Kohana::FILE_SECURITY . "\n\n";
			$data .= "/**\n";
			$data .= " * This is a PHP generated class providing interceptor capabilities for class '" . $clazz_name . "'.\n";
			$data .= " *\n";
			$data .= " * @author: " . __CLASS__ . "\n";
			$data .= " * @date: " . date("Y-m-d H:i:s") . "\n";
			$data .= " */\n";
			$data .= "class " . $proxy . " extends " . $clazz_name . " {\n";
			$methods = '';
			
			$content = file($clazz_file);
			foreach ($ref->getMethods() as $method) {
				foreach ($this->af->get_annotations($method) as $name => $annotation) {
					$annotation = (is_array($annotation)) ? current($annotation):$annotation;
					
					if ($annotation instanceof Interceptable) {
						$method_data = $method->getDocComment() ."\n";
						$method_data .= $content[($method->getStartLine()-1)] ."\n";
						
						$method_data .= "\n";
						$method_data .= "}\n";
						$data .= $method_data;
						break;
					}
				}
			}
			$data .= "}";
			
			$dir = dirname(APPPATH . 'classes/' . str_replace('_', '/', $proxy) . '.php');
			if (!file_exists($dir) && !@mkdir($dir, 0777, true)) {
				throw new Kohana_Exception("Can not create proxy class :file: Can not create directory!", array(
						":file" => APPPATH . 'classes/' . str_replace('_', '/', $proxy) . '.php' 
				));
			}
			
			if (file_put_contents(APPPATH . 'classes/' . str_replace('_', '/', $proxy) . '.php', $data) === false) {
				throw new Kohana_Exception("Can not create proxy class :file: Method failed!", array(
						":file" => APPPATH . 'classes/' . str_replace('_', '/', $proxy) . '.php' 
				));
			}
		}
		
		return new $proxy();
	}
}