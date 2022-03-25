<?php

namespace app\commands;

use Yii;
use function implode;
use function sprintf;
use function str_repeat;
use const PHP_EOL;

/**
 * 分类数据处理
 *
 * @package app\commands
 */
class CleanerController extends Controller
{

    /**
     * @param $module
     * @throws \yii\db\Exception
     */
    public function actionIndex($module)
    {
        $modules = [
            'g' => ['g_order', 'g_order_item', 'g_package', 'g_package_order_item'],
        ];
        if (isset($modules[$module])) {
            $tables = $modules[$module];
            $ok = $this->confirm("是否删除 {$module} 模块中的所有数据（" . implode(', ', $tables) . "）？");
            if ($ok) {
                $this->stdout(str_repeat('#', 80) . PHP_EOL);
                $cmd = Yii::$app->getDb()->createCommand();
                foreach ($tables as $i => $table) {
                    $this->stdout(sprintf(' > %d、Truncate %s table...', $i + 1, $table) . PHP_EOL);
                    $cmd->truncateTable("{{%$table}}")->execute();
                }
                $this->stdout(str_repeat('#', 80) . PHP_EOL);
            } else {
                $this->stdout(" > Ignore" . PHP_EOL);
            }
        } else {
            $this->stderr("Invalid module name.");
        }
        $this->stdout('Done.');
    }

}
