<?php

class XHProfRunPool {
  
  /**
   * Registered runs
   *
   * @var array
   */
  protected static $runs = array();
  
  /**
   * Pool singleton
   *
   * @var XHProfRunPool
   */
  protected static $instance;
  
  /**
   * Singleton accessor
   *
   * @return XHProfRunPool
   */
  public static function getInstance()
  {
    if(!self::$instance)
    {
      self::$instance = new XHProfRunPool();
    }
    
    return self::$instance;
  }
  
  /**
   * Associate a XHProfRun to the pool
   *
   * @param XHProfRun $run
   */
  public static function register(XHProfRun $run)
  {
    self::$runs[] = $run;
  }
  
  /**
   * Returns runs associated to the pool
   *
   * @return array
   */
  public static function getRuns()
  {
    return self::$runs;
  }
  
  /**
   * Returns runs associated to the pool within a namespace
   *
   * @return array
   */
  public static function getRunsByNamespace($withPrevious = false)
  {
    $runs = array();
    foreach(self::$runs as $run)
    {
      if(!isset($runs[$run->getNamespace()]))
      {
        $runs[$run->getNamespace()] = array();
      }
      $runs[$run->getNamespace()][] = $run;
    }

    if($withPrevious)
    {
      $currentRuns = $runs;
      $previousRuns = self::getPreviousRunsByNamespace();
      
      $namespaces = array_unique(array_merge(array_keys($currentRuns), array_keys($previousRuns)));
      sort($namespaces);
      
      $runs = array();
      foreach($namespaces as $namespace)
      {
        $current = isset($currentRuns[$namespace]) ? $currentRuns[$namespace] : array();
        $previous = isset($previousRuns[$namespace]) ? $previousRuns[$namespace] : array();
        usort($current, array('XHProfRunPool', 'sortByDate'));
        usort($previous, array('XHProfRunPool', 'sortByDate'));
        $runs[$namespace]['current'] = $current;
        $runs[$namespace]['previous'] = $previous;
      }
    }
    
    return $runs;
  }
  
  /**
   * Returns previous runs (stored on disk)
   *
   * @return array
   */
  public static function getPreviousRunsByNamespace()
  {
    $previous = array();
    $dir = ini_get("xhprof.output_dir");
    if(is_dir($dir))
    {
      foreach(sfFinder::type('file')->in($dir) as $runFile)
      {
        $infos = explode('.', basename($runFile));
        $runId = array_shift($infos);
        // In case the namespace contains dot character
        $runNamespace = implode('.', $infos);
        
        // We check that the run we are working on is not a run within the current session
        $found = false;
        foreach(self::$runs as $currentRun)
        {
          if($currentRun->getId()==$runId)
          {
            $found = true;
            break;
          }
        }
        
        if(!$found)
        {
          $run = new XHProfRun($runNamespace, false);
          $run->setId($runId);

          if(!isset($previous[$runNamespace]))
          {
            $previous[$runNamespace] = array();
          }
          $previous[$runNamespace][] = $run;
        }
      }
    }
    return $previous;
  }
  
  /**
   * Order by runs by descending date
   *
   * @param XHProfRun $run1
   * @param XHProfRun $run2
   * @return int
   */
  public static function sortByDate(XHProfRun $run1, XHProfRun $run2)
  {
    $d1 = $run1->getDate();
    $d2 = $run2->getDate();
    if($d1 == $d2)
    {
      return 0;
    }
    return $d1 > $d2  ? -1 : 1;
  }
}
