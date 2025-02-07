<?php

declare(strict_types=1);

namespace PhpMyAdmin\Tests;

use PhpMyAdmin\DatabaseInterface;
use PhpMyAdmin\SystemDatabase;
use PhpMyAdmin\Version;

/**
 * @covers \PhpMyAdmin\SystemDatabase
 */
class SystemDatabaseTest extends AbstractTestCase
{
    /**
     * SystemDatabase instance
     *
     * @var SystemDatabase
     */
    private $sysDb;

    /**
     * Setup function for test cases
     *
     * @access protected
     */
    protected function setUp(): void
    {
        parent::setUp();
        /**
         * SET these to avoid undefine d index error
         */
        $GLOBALS['server'] = 1;
        $GLOBALS['cfg']['Server']['pmadb'] = '';

        $dbi = $this->getMockBuilder(DatabaseInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $dbi->expects($this->any())
            ->method('tryQuery')
            ->will($this->returnValue('executeResult2'));

        //_SESSION
        $_SESSION['relation'][$GLOBALS['server']] = [
            'version' => Version::VERSION,
            'table_coords' => 'table_name',
            'displaywork' => true,
            'db' => 'information_schema',
            'table_info' => 'table_info',
            'relwork' => true,
            'commwork' => true,
            'pdfwork' => true,
            'mimework' => true,
            'column_info' => 'column_info',
            'relation' => 'relation',
        ];

        $dbi->expects($this->any())
            ->method('fetchAssoc')
            ->will(
                $this->returnValue(
                    [
                        'table_name' => 'table_name',
                        'column_name' => 'column_name',
                        'comment' => 'comment',
                        'mimetype' => 'mimetype',
                        'transformation' => 'transformation',
                        'transformation_options' => 'transformation_options',
                    ]
                )
            );

        $this->sysDb = new SystemDatabase($dbi);
    }

    /**
     * Tests for PMA_getExistingTransformationData() method.
     */
    public function testPMAGetExistingTransformationData(): void
    {
        $db = 'PMA_db';
        $ret = $this->sysDb->getExistingTransformationData($db);

        //validate that is the same as $dbi->tryQuery
        $this->assertEquals('executeResult2', $ret);
    }

    /**
     * Tests for PMA_getNewTransformationDataSql() method.
     */
    public function testPMAGetNewTransformationDataSql(): void
    {
        $db = 'PMA_db';
        $column_map = [
            [
                'table_name' => 'table_name',
                'refering_column' => 'column_name',
            ],
        ];
        $view_name = 'view_name';

        $ret = $this->sysDb->getNewTransformationDataSql(
            (object) [],
            $column_map,
            $view_name,
            $db
        );

        $sql = 'INSERT INTO `information_schema`.`column_info` '
            . '(`db_name`, `table_name`, `column_name`, `comment`, `mimetype`, '
            . '`transformation`, `transformation_options`) VALUES '
            . "('PMA_db', 'view_name', 'column_name', 'comment', 'mimetype', "
            . "'transformation', 'transformation_options')";

        $this->assertEquals($sql, $ret);
    }
}
