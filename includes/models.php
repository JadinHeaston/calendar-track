<?PHP

class DatabaseConnector
{
	protected $connection;
	protected $type;

	private $queries = array(
		'listTables' => array(
			'mysql' => 'SHOW FULL tables',
			'sqlite' => 'SELECT * FROM sqlite_schema WHERE type =\'table\' AND name NOT LIKE \'sqlite_%\'',
			'sqlsrv' => 'SELECT DISTINCT TABLE_NAME FROM information_schema.tables'
		),
		'getTableInformation' => array(
			'mysql' => 'DESCRIBE ?',
			'sqlite' => 'PRAGMA table_info(?)',
			'sqlsrv' => 'SELECT * FROM information_schema.columns WHERE TABLE_NAME = ? order by ORDINAL_POSITION'
		),
		'getTableIndexes' => array(
			'mysql' => 'SHOW INDEX FROM ?',
			'sqlite' => 'SELECT * FROM sqlite_master WHERE type = \'index\' AND tbl_name = ?',
			'sqlsrv' => 'SELECT * FROM sys.indexes WHERE object_id = (SELECT object_id FROM sys.objects WHERE name = ?)'
		),
		'getTableCreation' => array(
			'mysql' => 'SHOW CREATE TABLE ?',
			'sqlite' => 'SELECT sql FROM sqlite_schema WHERE name = ?',
			'sqlsrv' => false //Not available without a stored procedure.
		),
		'createTable' => array(
			'mysql' => 'CREATE TABLE IF NOT EXISTS ? ()',
			'sqlite' => 'CREATE TABLE IF NOT EXISTS ? (column_name datatype, column_name datatype);',
			'sqlsrv' => ''
		)
	);

	public function __construct(string $type, string $hostPath, int $port = null, string $db = '', string $user = '', string $pass = '', string $charset = 'utf8mb4', bool|null $trustCertificate = null)
	{
		$this->type = strtolower(trim($type));
		try
		{
			//Creating DSN string.
			$dsn = $this->type;
			if ($this->type === 'mysql')
				$dsn .= ':host=';
			elseif ($this->type === 'sqlite')
				$dsn .= ':';
			elseif ($this->type === 'sqlsrv')
				$dsn .= ':Server=';

			$dsn .= $hostPath;

			if ($this->type === 'mysql')
				$dsn .= ';port=' . strval($port);

			if ($this->type === 'mysql')
				$dsn .= ';dbname=';
			elseif ($this->type === 'sqlsrv')
				$dsn .= ';Database=';

			$dsn .= $db;

			if ($this->type === 'mysql')
				$dsn .= ';charset=' . $charset;
			if ($this->type === 'sqlsrv' && $trustCertificate !== null)
				$dsn .= ';TrustServerCertificate=' . strval(intval($trustCertificate));

			//Attempting connection.
			$this->connection = new \PDO($dsn, $user, $pass);
			$this->connection->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_WARNING);
			$this->connection->setAttribute(\PDO::ATTR_EMULATE_PREPARES, false);
			$this->connection->setAttribute(\PDO::ATTR_DEFAULT_FETCH_MODE, \PDO::FETCH_ASSOC);
		}
		catch (\PDOException $e)
		{
			exit($e->getMessage());
		}

		return $this->connection;
	}

	public function executeStatement($query = '', $params = [], $skipPrepare = false)
	{
		try
		{
			if ($skipPrepare !== true)
			{
				$stmt = $this->connection->prepare($query);

				if ($stmt === false)
					throw new \Exception('Unable to do prepared statement: ' . $query);

				$stmt->execute($params);
				return $stmt;
			}
			else
				return $this->connection->exec($query);
		}
		catch (\Exception $e)
		{
			throw new \Exception($e->getMessage());
		}
	}

	public function select($query = '', $params = [])
	{
		try
		{
			$stmt = $this->executeStatement($query, $params);
			return $stmt->fetchAll();
		}
		catch (\Exception $e)
		{
			throw new \Exception($e->getMessage());
		}
		return false;
	}

	public function update($query = '', $params = [])
	{
		try
		{
			$stmt = $this->executeStatement($query, $params);
			return $stmt->rowCount();
		}
		catch (\Exception $e)
		{
			throw new \Exception($e->getMessage());
		}
		return false;
	}

	public function getLastInsertID(): string
	{
		return $this->connection->lastInsertId();
	}

	public function listTables($includeViews = true)
	{
		$query = $this->queries[__FUNCTION__][$this->type];
		if ($query === false)
			return false;

		if ($includeViews === false && $this->type === 'mysql')
			$query .= ' WHERE Table_Type = \'BASE TABLE\'';
		elseif ($includeViews === false && $this->type === 'sqlsrv')
			$query .= ' WHERE TABLE_TYPE = \'BASE TABLE\'';

		try
		{
			$stmt = $this->executeStatement($query);
			return $stmt->fetchAll();
		}
		catch (\Exception $e)
		{
			throw new \Exception($e->getMessage());
		}
		return false;
	}

	public function getTableInformation(string $table)
	{
		$query = $this->queries[__FUNCTION__][$this->type];
		if ($query === false)
			return false;

		elseif ($this->type === 'sqlite')
			$query = 'PRAGMA table_info(?)';
		elseif ($this->type === 'sqlsrv')
			$query = 'SELECT * FROM information_schema.columns WHERE TABLE_NAME = ? order by ORDINAL_POSITION';
		try
		{
			$stmt = $this->executeStatement($query, array($table));
			return $stmt->fetchAll();
		}
		catch (\Exception $e)
		{
			throw new \Exception($e->getMessage());
		}
		return false;
	}

	public function getTableIndexes(string $table)
	{
		$query = $this->queries[__FUNCTION__][$this->type];
		if ($query === false)
			return false;

		try
		{
			$stmt = $this->executeStatement($query, array($table));
			return $stmt->fetchAll();
		}
		catch (\Exception $e)
		{
			throw new \Exception($e->getMessage());
		}
		return false;
	}

	public function getTableCreation(string $table)
	{
		$query = $this->queries[__FUNCTION__][$this->type];
		if ($query === false)
			return false;

		try
		{
			$stmt = $this->executeStatement($query, array($table));
			return $stmt->fetchAll();
		}
		catch (\Exception $e)
		{
			throw new \Exception($e->getMessage());
		}
		return false;
	}

	//$columns is expected to follow the structure below:
	// [
	// 	0 => array(
	// 		'name' => '',
	// 		'type' => '',
	// 		'index' => false,
	// 		'primary' => false,
	// 		'null' => false,
	// 		'default' => '', //Any type.
	// 		'foreign_key' => array()
	// 	),
	// ]
	public function createTable(string $tableName, array $columns)
	{
		$query = $this->queries[__FUNCTION__][$this->type];
		if ($query === false)
			return false;

		try
		{
			$stmt = $this->executeStatement($query, array($tableName,));
			return $stmt->fetchAll();
		}
		catch (\Exception $e)
		{
			throw new \Exception($e->getMessage());
		}

		return false;
	}
}

class CTConnector extends DatabaseConnector
{
	public function getCalendar(int $calendarID = 0, bool $includeICS = false)
	{
		$query = 'SELECT id, name, enable_weather' . ($includeICS === true ? ', ics_link' : '') . ' FROM calendar WHERE active = TRUE';
		$params = [];
		if ($calendarID !== 0)
		{
			$query .= ' AND id = ?';
			$params[] = $calendarID;
		}

		return $this->select($query, $params);
	}
	public function getCalendarWeatherToggle(int $calendarID)
	{
		$query = 'SELECT enable_weather FROM calendar WHERE active = TRUE AND id = ?';

		$results = $this->select($query, [$calendarID]);
		if ($results !== false && count($results) === 1)
			return boolval(intval($results[0]['enable_weather']));
		else
			return false;
	}

	public function removeCalendar(int $calendarID)
	{
		$result = $this->executeStatement('DELETE FROM calendar WHERE id = ?', [$calendarID]);
		if ($result !== false)
			return true;
		else
			return false;
	}

	public function editCalendarName(int $calendarID, string $newName, string $newICSLink)
	{
		$result = $this->executeStatement('UPDATE calendar SET name = ?, ics_link = ? WHERE id = ?', [htmlspecialchars($newName), $newICSLink, $calendarID]);
		if ($result !== false)
			return true;
		else
			return false;
	}

	public function addCalendar(string $name, string $icsLink)
	{
		$result = $this->executeStatement('INSERT INTO calendar (name, ics_link) VALUES (?, ?)', [htmlspecialchars($name), $icsLink]);
		if ($result !== false)
			return $this->getLastInsertID();
		else
			return false;
	}
}

class ScopeTimer
{
	public $name;
	public $startTime;

	function __construct($name = 'Timer')
	{
		$this->startTime = microtime(true);
		$this->name = $name;
	}

	function __destruct()
	{
		$elapsed_time = microtime(true) - $this->startTime;
		echo $this->name . ': ' . $elapsed_time . ' S';
	}

	//timer = new ScopeTimer(__FILE__);
}
