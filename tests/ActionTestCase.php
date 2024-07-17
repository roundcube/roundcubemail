<?php

/**
 * Test class to test rcmail_action_mail_index
 *
 * @package Tests
 */
class ActionTestCase extends PHPUnit\Framework\TestCase
{
    static $files = [];


    static function setUpBeforeClass(): void
    {
        // reset some interfering globals set in other tests
        $_SERVER['REQUEST_URI'] = '';

        $rcmail = rcmail::get_instance();
        $rcmail->load_gui();
    }

    static function tearDownAfterClass(): void
    {
        foreach (self::$files as $file) {
            unlink($file);
        }

        self::$files = [];

        $rcmail = rcmail::get_instance();
        $rcmail->shutdown();

        $_FILES = [];
        rcmail::get_instance()->storage->methodCalls = [];
    }

    public function setUp(): void
    {
        $_GET     = [];
        $_POST    = [];
        $_REQUEST = [];
    }

    /**
     * Initialize the testing suite
     */
    public static function init()
    {
        self::initSession();
        self::initDB();
        self::initUser();
        self::initStorage();
    }

    /**
     * Initialize "mocked" output class
     */
    protected static function initOutput($mode, $task, $action, $framed = false)
    {
        $rcmail = rcmail::get_instance();

        $rcmail->task = $task;
        $rcmail->action = $action;

        if ($mode == rcmail_action::MODE_AJAX) {
            return $rcmail->output = new OutputJsonMock();
        }

        $rcmail->output = new OutputHtmlMock($task, $framed);

        if ($framed) {
            $rcmail->comm_path .= '&_framed=1';
            $rcmail->output->set_env('framed', true);
        }

        $rcmail->output->set_env('task', $task);
        $rcmail->output->set_env('action', $action);
        $rcmail->output->set_env('comm_path', $rcmail->comm_path);
        $rcmail->output->set_charset(RCUBE_CHARSET);

        return $rcmail->output;
    }

    /**
     * Wipe and re-initialize database
     */
    public static function initDB($file = null)
    {
        $rcmail = rcmail::get_instance();
        $dsn    = rcube_db::parse_dsn($rcmail->config->get('db_dsnw'));
        $db     = $rcmail->get_dbh();

        if ($file) {
            self::loadSQLScript($db, $file);
            return;
        }

        if ($dsn['phptype'] == 'mysql' || $dsn['phptype'] == 'mysqli') {
            // drop all existing tables first
            $db->query("SET FOREIGN_KEY_CHECKS=0");
            $sql_res = $db->query("SHOW TABLES");
            while ($sql_arr = $db->fetch_array($sql_res)) {
                $table = reset($sql_arr);
                $db->query("DROP TABLE $table");
            }

            // init database with schema
            system(sprintf('cat %s %s | mysql -h %s -u %s --password=%s %s',
                realpath(INSTALL_PATH . '/SQL/mysql.initial.sql'),
                realpath(TESTS_DIR . 'src/sql/init.sql'),
                escapeshellarg($dsn['hostspec']),
                escapeshellarg($dsn['username']),
                escapeshellarg($dsn['password']),
                escapeshellarg($dsn['database'])
            ));
        }
        else if ($dsn['phptype'] == 'sqlite') {
            $db->closeConnection();
            // delete database file
            system(sprintf('rm -f %s', escapeshellarg($dsn['database'])));

            // load sample test data
            self::loadSQLScript($db, 'init');
        }
    }

    /**
     * Set the $rcmail->user property
     */
    public static function initUser()
    {
        $rcmail = rcmail::get_instance();
        $rcmail->set_user(new rcube_user(1));
    }

    /**
     * Set the $rcmail->session property
     */
    public static function initSession()
    {
        $rcmail = rcmail::get_instance();
        $rcmail->session = new rcube_session_php($rcmail->config);
    }

    /**
     * Set the $rcmail->storage property
     *
     * @return StorageMock The storage object
     */
    public static function initStorage()
    {
        $rcmail = rcmail::get_instance();
        $rcmail->storage = new StorageMock();

        return $rcmail->storage;
    }

    /**
     * Create a temp file
     */
    protected function createTempFile($content = '')
    {
        $file = rcube_utils::temp_filename('tests');

        if ($content !== '') {
            file_put_contents($file, $content);
        }

        self::$files[] = $file;

        return $file;
    }

    /**
     * Load an execute specified SQL script
     */
    protected static function loadSQLScript($db, $name)
    {
        // load sample test data
        // Note: exec_script() does not really work with these queries
        $sql = file_get_contents(TESTS_DIR . "src/sql/{$name}.sql");
        $sql = preg_split('/;\n/', $sql, -1, PREG_SPLIT_NO_EMPTY);

        foreach ($sql as $query) {
            $result = $db->query($query);
            if ($db->is_error($result)) {
                rcube::raise_error($db->is_error(), false, true);
            }
        }
    }

    /**
     * Call the action's run() method and handle exit exception
     */
    protected function runAndAssert($action, $expected_code, $args = [])
    {
        // Reset output in case we execute the method multiple times in a single test
        $rcmail = rcmail::get_instance();
        $rcmail->output->reset(true);

        // reset some static props
        setProperty($action, 'edit_form', null);

        try {
            StderrMock::start();
            $action->run($args);
            StderrMock::stop();
        }
        catch (ExitException $e) {
            $this->assertSame($expected_code, $e->getCode());
        }
        catch (Exception $e) {
            if ($e->getMessage() == 'Error raised' && $expected_code == OutputHtmlMock::E_EXIT) {
                return;
            }

            echo StderrMock::$output;
            throw $e;
        }
    }
}
