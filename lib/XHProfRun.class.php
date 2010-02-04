<?php
class XHProfRun
{
  
  /**
   * Namespace du run
   *
   * @var string
   */
  protected $namespace;
  
  /**
   * Id du run
   *
   * @var string
   */
  protected $id;
  
  /**
   * Id du run
   *
   * @var string
   */
  protected $data;
  
  /**
   * Etat du run
   *
   * @var string
   */
  protected $started = false;
  
  /**
   * Date du run
   *
   * @var string
   */
  protected $date;
  
  /**
   * Constructeur du run, le run est automatiquement démarré
   *
   * @param string $namespace
   */
  public function __construct($namespace, $autostart = true)
  {
    $this->namespace = $namespace;
    
    if ($autostart)
    {
      $this->start();
    }
  }
  
  /**
   * Setter pour l'id
   *
   * @param string $id
   */
  public function setId($id)
  {
    $this->id = $id;
  }
  
  /**
   * Démarrage du profiling
   *
   */
  public function start()
  {
    if (!extension_loaded('xhprof'))
    {
      throw new sfException("XHProf extension is not loaded.");
    }
    if (!$this->started)
    {
      xhprof_enable(XHPROF_FLAGS_CPU + XHPROF_FLAGS_MEMORY);
      $this->started = true;
    }
  }
  
  /**
   * Déclenche la fin du run, l'enregistrement des données et l'association du run au pool
   *
   */
  public function end()
  {
    if (!extension_loaded('xhprof'))
    {
      throw new sfException("XHProf extension is not loaded.");
    }
    if ($this->started)
    {
      $this->data = xhprof_disable();
      
      require_once(dirname(__FILE__).'/vendor/facebook/xhprof/utils/xhprof_runs.php');
      $xhprof_runs = new XHProfRuns_Default();
      $this->id = $xhprof_runs->save_run($this->data, $this->namespace);
      
      $this->register();
    }
    else
    {
      throw new sfException("This run has not been started.");
    }
  }
  
  /**
   * Getter sur l'id du run
   *
   * @return string
   */
  public function getId()
  {
    return $this->id;
  }
  
  /**
   * Getter sur le namespace
   *
   * @return string
   */
  public function getNamespace()
  {
    return $this->namespace;
  }
  
  /**
   * Génération de l'url de consultation du run
   *
   * @return string
   */
  public function getUrl()
  {
    return sprintf('%s?run=%s&source=%s', _compute_public_path('index.php', 'elXHProfPlugin', 'php'),$this->getId(), $this->getNamespace());
  }
  
  /**
   * Enregistrement du run au pool
   *
   */
  protected function register()
  {
    $pool = XHProfRunPool::getInstance();
    
    $pool->register($this);
  }
  
  /**
   * Returns run timestamp
   *
   * @return int
   */
  public function getDate()
  {
    return filemtime($this->getFilename());
  }
  
  /**
   * Returns filepath of the data file
   *
   * @return string
   */
  public function getFilename()
  {
    return sprintf('%s/%s.%s', ini_get("xhprof.output_dir"), $this->getId(), $this->getNamespace());
  }
}
