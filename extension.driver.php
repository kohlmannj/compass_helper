<?php
  Class extension_compass_helper extends Extension
  {
    public $workspace_position = NULL;
    public $compass_exec = "compass compile";
    
    /*-------------------------------------------------------------------------
      Extension definition
    -------------------------------------------------------------------------*/
    
    public function about()
    {
      return array(
        'name' => 'Compass Helper',
        'version' => '1.0.0',
        'release-date' => '2012-02-18',
        'author' => array(
          'name' => 'Joe Kohlmann (original by Max Wheeler)',
          'email' => 'kohlmannj@gmail.com',
        )
      );
    }
 
    public function getSubscribedDelegates()
    {
      return array(
        array(
          'page' => '/frontend/',
          'delegate' => 'FrontendOutputPostGenerate',
          'callback' => 'find_matches'          
        )
      );
    }
    
  	/*-------------------------------------------------------------------------
  		Delegates
  	-------------------------------------------------------------------------*/
    public function find_matches(&$context)
    {
      $context['output'] = preg_replace_callback('/(\"|\')(([^\"\']+).(sass|scss))/', array(&$this, '__replace_matches'), $context['output']);
    }
    
  	/*-------------------------------------------------------------------------
  		Helpers
  	-------------------------------------------------------------------------*/
    private function __replace_matches($matches)
    {
      $this->workspace_position = strpos($matches[2], 'workspace');
      if (!$this->workspace_position) $this->workspace_position = 1;
      
	  $extension = "." . $matches[4];
	  
      $path = DOCROOT . "/" . substr($matches[2], $this->workspace_position);
      $path = $this->__generate_css($path, $extension);
      $mtime = @filemtime($path);
      
      return '"' . str_replace("/sass/", "/css/", $matches[3]) . ($mtime ? '.css?' . 'mod-' . $mtime : NULL);
    }
    
    private function __generate_css($filename, $extension)
    {
      # Setup .css and .sass filenames
      $sass_filename = $filename;
      $css_filename = str_replace("/sass/", "/css/", str_replace($extension, '.css', $filename));
      
      # If Sass doesn't exist, throw an error in the CSS
      if ( ! file_exists($sass_filename))
      {
        file_put_contents($css_filename, "/** Error: Sass file not found **/");
      }
      else if (!file_exists($css_filename) OR filemtime($css_filename) < filemtime($sass_filename))
      {
        @unlink($css_filename);
        # Generate .css via shell command
        echo exec($this->compass_exec . ' ' . WORKSPACE);
      }
      
      return $css_filename;
    }
  }
?>