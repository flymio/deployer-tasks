<?php

/**
 * Do with this what you want.
 *
 * (c) Alexey Popov <6425762@gmail.com>
 */
namespace Tasks;

/**
 * Libs dir
 *
 * @var string
 */
define('LIBS_DIR', __DIR__ . '/lib/');
use Symfony\Component\Finder\Finder;
abstract class Min extends \Deployer\Task
{
    /**
     * Default name param array
     *
     * @var array
     */
    protected $_defaultName = array();
    /**
     * Execute task and return report info
     *
     * @return \Deployer\Task\Min
     */
    public function execute()
    {
        $info = '';
        $dir = \Deployer::applyGlobalParams($this->param('dir'));
        $finder = new Finder();
        $iterator = $finder->files();
        $overallDecrease = 0;
        foreach ($this->param('name') as $name) {
            $iterator->name(\Deployer::applyGlobalParams($name));
        }
        $files = $iterator->in($dir);
        foreach ($files as $file) {
            $fileName = str_replace('/', DIRECTORY_SEPARATOR, $file);
            list($decreasePrecent, $decreaseBytes) = $this->_min($fileName);
            $overallDecrease += $decreaseBytes;
            $info .= sprintf('%s file decreased at %01.2f%% (%s)', \Deployer::censor($fileName), $decreasePrecent, \Deployer::size($decreaseBytes)) . PHP_EOL;
        }
        $message = sprintf('%d files minifyed at %s', sizeof($files), \Deployer::size($overallDecrease));
        \Deployer::messageInfo($message);
        $info .= $message . PHP_EOL;
        if (!empty($info)) {
            return $this->info($info);
        }
        return $this;
    }
    /**
     * Prepares input params
     *
     * @param array $params
     * @return array
     */
    protected function _prepare(array $params)
    {
        if (!isset($params['dir'])) {
            throw new \Deployer\TaskException('"dir" param is required');
        }
        if (!isset($params['name'])) {
            $params['name'] = $this->_defaultName;
        }
        return array('dir' => $params['dir'], 'name' => (array) $params['name']);
    }
    /**
     * Minimizes file
     *
     * @param stgring $fileName
     * @return array
     */
    protected abstract function _min($fileName);
}
