<?php

/**
 * Class du paneau de debug pour XHProf
 *
 */
class elWebDebugPanelXHProf extends sfWebDebugPanel
{
  /**
   * Renvoie le titre
   *
   * @return string
   */
  public function getTitle()
  {
    return 'XHprof';
  }
 
  /**
   * Renvoie le titre du panneau
   *
   * @return string
   */
  public function getPanelTitle()
  {
    return 'Profiling XHProf';
  }
 
  /**
   * Renvoie le contenu du paneau
   *
   * @return string
   */
  public function getPanelContent()
  {
    $types = array('previous' => 'previous sessions', 'current' => "current session");

    $html = '';
    foreach(XHProfRunPool::getRunsByNamespace(true) as $namespace => $allRuns)
    {
      $html .= sprintf('<h2>Namespace : %s<a href="#" onclick="sfWebDebugToggle(\'pmsipilotWebDebugXHProf-%s\'); return false;"><img src="'.$this->webDebug->getOption('image_root_path').'/toggle.gif"/></a></h2>', $namespace, md5($namespace));
      $html .= sprintf('<div id="pmsipilotWebDebugXHProf-%s" style="display: none;">', md5($namespace));

      foreach($allRuns as $type => $runs)
      {
        $links = array();
        $html .= sprintf("<h3>Runs from %s</h3>", $types[$type]);
        foreach($runs as $run)
        {
          $links[] = sprintf('<input type="checkbox" name="runs-%s" id="run-%s"><a href="%s" target="_blank">Run %s (%s)</a>', md5($namespace), $run->getId(), $run->getUrl(), $run->getId(), date('Y-m-d H:i:s', $run->getDate()));
        }
        if(count($links))
        {
          $html .= sprintf('<ol style="margin-left: 0px; list-style-type: none;" class="runs-container-%s"><li>%s</li></ol>', md5($namespace), implode('</li><li>', $links));
        }
        else
        {
          $html .= "No run";
        }
      }
      $html .= sprintf('<input type="button" id="runs-%s" value="Compare" onclick="xhprofCompare(this, \'%s\', \'%s\')">', md5($namespace), md5($namespace), addslashes($namespace));
      $html .= '</div>';
    }
    
    $baseUrl = _compute_public_path('index.php', 'elXHProfPlugin', 'php');
    $html .=<<<EOF
<script type="text/javascript">
function xhprofCompare(button, namespaceId, namespace) {
  var groupId = button.id;
  var groupNamespace = button.id.split('-')[1];
    
  var checkedElements = [];
  var runContainers = sfWebDebugGetElementsByClassName('runs-container-'+namespaceId);
  for(var i = 0; i<runContainers.length; i++)
  {
    var runCheckboxes = runContainers[i].getElementsByTagName('input');
    for(var j = 0; j<runCheckboxes.length; j++)
    {
      if(runCheckboxes[j].checked)
      {
        checkedElements.push(runCheckboxes[j]);
      }
    }
  }
  
  if(checkedElements.length==2) {
    var run1 = checkedElements[1].id.split('-')[1];
    var run2 = checkedElements[0].id.split('-')[1];
    window.open("$baseUrl?run1="+run1+"&run2="+run2+"&source="+namespace);
  }
  else
  {
    alert("We can only compare 2 runs.");
  }
}
</script>
EOF;
    
    return $html;
  }
  
  public static function listenToAddPanelEvent(sfEvent $event)
  {
    $event->getSubject()->setPanel('xhprof', new elWebDebugPanelXHProf($event->getSubject()));
  }
}
