<?php

namespace mageekguy\atoum\tests\units;

use
	mageekguy\atoum,
	mageekguy\atoum\mock
;

require_once __DIR__ . '/../runner.php';

class runner extends atoum\test
{
	public function testClass()
	{
		$this->assert
			->testedClass
				->hasInterface('mageekguy\atoum\observable')
			->string(atoum\runner::atoumVersionConstant)->isEqualTo('mageekguy\atoum\version')
			->string(atoum\runner::atoumDirectoryConstant)->isEqualTo('mageekguy\atoum\directory')
			->string(atoum\runner::runStart)->isEqualTo('runnerStart')
			->string(atoum\runner::runStop)->isEqualTo('runnerStop')
		;
	}

	public function test__construct()
	{
		$this
			->if($runner = new atoum\runner())
			->then
				->object($runner->getScore())->isInstanceOf('mageekguy\atoum\score')
				->object($runner->getAdapter())->isInstanceOf('mageekguy\atoum\adapter')
				->object($runner->getLocale())->isInstanceOf('mageekguy\atoum\locale')
				->object($runner->getIncluder())->isInstanceOf('mageekguy\atoum\includer')
				->object($runner->getTestDirectoryIterator())->isInstanceOf('mageekguy\atoum\iterators\recursives\directory\factory')
				->object($defaultGlobIteratorFactory = $runner->getGlobIteratorFactory())->isInstanceOf('closure')
				->object($defaultGlobIteratorFactory($pattern = uniqid()))->isEqualTo(new \globIterator($pattern))
				->object($defaultReflectionClassFactory = $runner->getReflectionClassFactory())->isInstanceOf('closure')
				->object($defaultReflectionClassFactory($this))->isEqualTo(new \reflectionClass($this))
				->variable($runner->getRunningDuration())->isNull()
				->boolean($runner->codeCoverageIsEnabled())->isTrue()
				->variable($runner->getDefaultReportTitle())->isNull()
				->array($runner->getObservers())->isEmpty()
		;
	}

	public function testSetAdapter()
	{
		$this
			->if($runner = new atoum\runner())
			->then
				->object($runner->setAdapter($adapter = new atoum\test\adapter()))->isIdenticalTo($runner)
				->object($runner->getAdapter())->isIdenticalTo($adapter)
		;
	}

	public function testSetScore()
	{
		$this
			->if($runner = new atoum\runner())
			->then
				->object($runner->setScore($score = new atoum\runner\score()))->isIdenticalTo($runner)
				->object($runner->getScore())->isIdenticalTo($score);
		;
	}

	public function testSetDefaultReportTtitle()
	{
		$this
			->if($runner = new atoum\runner())
			->then
				->object($runner->setDefaultReportTitle($title = uniqid()))->isIdenticalTo($runner)
				->string($runner->getDefaultReportTitle())->isEqualTo($title)
		;
	}

	public function testGetPhpPath()
	{
		$this
			->if($runner = new atoum\runner())
			->then
				->string($runner->getPhpPath())->isEqualTo($runner->getPhp()->getBinaryPath())
		;
	}

	public function testSetPhpPath()
	{
		$this
			->if($runner = new atoum\runner())
			->then
				->object($runner->setPhpPath($phpPath = uniqid()))->isIdenticalTo($runner)
				->string($runner->getPhpPath())->isIdenticalTo($phpPath)
		;
	}

	public function runnerEnableDebugMode()
	{
		$this
			->if($runner = new atoum\runner())
			->then
				->object($runner->enableDebugMode())->isIdenticalTo($runner)
				->boolean($runner->debugModeIsEnabled())->isTrue()
				->object($runner->enableDebugMode())->isIdenticalTo($runner)
				->boolean($runner->debugModeIsEnabled())->isTrue()
		;
	}

	public function runnerDisableDebugMode()
	{
		$this
			->if($runner = new atoum\runner())
			->then
				->object($runner->disableDebugMode())->isIdenticalTo($runner)
				->boolean($runner->debugModeIsEnabled())->isFalse()
				->object($runner->disableDebugMode())->isIdenticalTo($runner)
				->boolean($runner->debugModeIsEnabled())->isFalse()
			->if($runner->enableDebugMode())
			->then
				->object($runner->disableDebugMode())->isIdenticalTo($runner)
				->boolean($runner->debugModeIsEnabled())->isFalse()
		;
	}

	public function testAddObserver()
	{
		$this
			->if($runner = new atoum\runner())
			->then
				->array($runner->getObservers())->isEmpty()
				->object($runner->addObserver($observer = new \mock\mageekguy\atoum\observers\runner()))->isIdenticalTo($runner)
				->array($runner->getObservers())->isEqualTo(array($observer))
		;
	}

	public function testRemoveObserver()
	{
		$this
			->if($runner = new atoum\runner())
			->then
				->array($runner->getObservers())->isEmpty()
				->object($runner->removeObserver(new \mock\mageekguy\atoum\observers\runner()))->isIdenticalTo($runner)
				->array($runner->getObservers())->isEmpty()
			->if($runner->addObserver($observer1 = new \mock\mageekguy\atoum\observers\runner()))
			->and($runner->addObserver($observer2 = new \mock\mageekguy\atoum\observers\runner()))
			->then
				->array($runner->getObservers())->isEqualTo(array($observer1, $observer2))
				->object($runner->removeObserver(new \mock\mageekguy\atoum\observers\runner()))->isIdenticalTo($runner)
				->array($runner->getObservers())->isEqualTo(array($observer1, $observer2))
				->object($runner->removeObserver($observer1))->isIdenticalTo($runner)
				->array($runner->getObservers())->isEqualTo(array($observer2))
				->object($runner->removeObserver($observer2))->isIdenticalTo($runner)
				->array($runner->getObservers())->isEmpty()
		;
	}

	public function testCallObservers()
	{
		$this
			->if($runner = new atoum\runner())
			->then
				->object($runner->callObservers(atoum\runner::runStart))->isIdenticalTo($runner)
			->if($runner->addObserver($observer = new \mock\mageekguy\atoum\observers\runner()))
			->then
				->object($runner->callObservers(atoum\runner::runStart))->isIdenticalTo($runner)
				->mock($observer)->call('handleEvent')->withArguments(atoum\runner::runStart, $runner)->once()
		;
	}

	public function testGetRunningDuration()
	{
		$this
			->if($adapter = new atoum\test\adapter())
			->and($adapter->microtime = function() { static $call = 0; return (++$call * 100); })
			->and($adapter->get_declared_classes = array())
			->and($runner = new atoum\runner())
			->and($runner->setAdapter($adapter))
			->then
				->variable($runner->getRunningDuration())->isNull()
			->if($runner->run())
			->then
				->integer($runner->getRunningDuration())->isEqualTo(100)
		;
	}

	public function testGetTestNumber()
	{
		$this
			->if($adapter = new atoum\test\adapter())
			->and($adapter->get_declared_classes = array())
			->and($runner = new atoum\runner())
			->and($runner->setAdapter($adapter))
			->then
				->integer($runner->getTestNumber())->isZero()
			->if($runner->run())
			->then
				->integer($runner->getTestNumber())->isZero();
		;
	}

	public function testGetTestMethodNumber()
	{
		$this
			->if($adapter = new atoum\test\adapter())
			->and($adapter->get_declared_classes = array())
			->and($runner = new atoum\runner())
			->and($runner->setAdapter($adapter))
			->then
				->integer($runner->getTestMethodNumber())->isZero()
			->if($runner->run())
			->then
				->integer($runner->getTestMethodNumber())->isZero()
		;
	}

	public function testGetBootstrapFile()
	{
		$this
			->if($runner = new atoum\runner())
			->and($includer = new \mock\mageekguy\atoum\includer())
			->and($includer->getMockController()->includePath = function() {})
			->and($runner->setIncluder($includer))
			->then
				->object($runner->setBootstrapFile($path = uniqid()))->isIdenticalTo($runner)
				->string($runner->getBootstrapFile())->isEqualTo($path)
				->mock($includer)->call('includePath')->withArguments($path)->once()
		;
	}

	public function testHasReports()
	{
		$this
			->if($runner = new atoum\runner())
			->then
				->boolean($runner->hasReports())->isFalse()
			->if($runner->addReport(new atoum\reports\realtime\cli()))
			->then
				->boolean($runner->hasReports())->isTrue()
		;
	}

	public function testAddReport()
	{
		$this
			->if($runner = new atoum\runner())
			->then
				->object($runner->addReport($report = new atoum\reports\realtime\cli()))->isIdenticalTo($runner)
				->array($runner->getReports())->isEqualTo(array($report))
				->array($runner->getObservers())->contains($report)
		;
	}

	public function testRemoveReport()
	{
		$this
			->if($runner = new atoum\runner())
			->then
				->array($runner->getReports())->isEmpty()
				->array($runner->getObservers())->isEmpty()
				->object($runner->removeReport(new atoum\reports\realtime\cli()))->isIdenticalTo($runner)
				->array($runner->getReports())->isEmpty()
				->array($runner->getObservers())->isEmpty()
			->if($report1 = new \mock\mageekguy\atoum\report())
			->and($report2 = new \mock\mageekguy\atoum\report())
			->and($runner->addReport($report1)->addReport($report2))
			->then
				->array($runner->getReports())->isEqualTo(array($report1, $report2))
				->array($runner->getObservers())->isEqualTo(array($report1, $report2))
				->object($runner->removeReport(new atoum\reports\realtime\cli()))->isIdenticalTo($runner)
				->array($runner->getReports())->isEqualTo(array($report1, $report2))
				->array($runner->getObservers())->isEqualTo(array($report1, $report2))
				->object($runner->removeReport($report1))->isIdenticalTo($runner)
				->array($runner->getReports())->isEqualTo(array($report2))
				->array($runner->getObservers())->isEqualTo(array($report2))
				->object($runner->removeReport($report2))->isIdenticalTo($runner)
				->array($runner->getReports())->isEmpty()
				->array($runner->getObservers())->isEmpty()
		;
	}

	public function testRemoveReports()
	{
		$this
			->if($runner = new atoum\runner())
			->then
				->array($runner->getReports())->isEmpty()
				->array($runner->getObservers())->isEmpty()
				->object($runner->removeReports())->isIdenticalTo($runner)
				->array($runner->getReports())->isEmpty()
				->array($runner->getObservers())->isEmpty()
			->if($report1 = new \mock\mageekguy\atoum\report())
			->and($report2 = new \mock\mageekguy\atoum\report())
			->and($runner->addReport($report1)->addReport($report2))
			->then
				->array($runner->getReports())->isEqualTo(array($report1, $report2))
				->array($runner->getObservers())->isEqualTo(array($report1, $report2))
				->object($runner->removeReports())->isIdenticalTo($runner)
				->array($runner->getReports())->isEmpty()
				->array($runner->getObservers())->isEmpty()
		;
	}

	public function testEnableCodeCoverage()
	{
		$this
			->if($runner = new atoum\runner())
			->and($runner->disableCodeCoverage())
			->then
				->boolean($runner->codeCoverageIsEnabled())->isFalse()
				->object($runner->enableCodeCoverage())->isIdenticalTo($runner)
				->boolean($runner->codeCoverageIsEnabled())->isTrue()
		;
	}

	public function testDisableCodeCoverage()
	{
		$this
			->if($runner = new atoum\runner())
			->and($runner->enableCodeCoverage())
			->then
				->boolean($runner->codeCoverageIsEnabled())->isTrue()
				->object($runner->disableCodeCoverage())->isIdenticalTo($runner)
				->boolean($runner->codeCoverageIsEnabled())->isFalse()
		;
	}

	public function testSetPathAndVersionInScore()
	{
		$this
			->if($php = new \mock\mageekguy\atoum\php())
			->and($this->calling($php)->getBinaryPath = $phpPath = uniqid())
			->and($this->calling($php)->execute = function() {})
			->and($this->calling($php)->isRunning = false)
			->and($this->calling($php)->getExitCode = 0)
			->and($this->calling($php)->getStdout = $phpVersion = uniqid())
			->and($adapter = new atoum\test\adapter())
			->and($adapter->defined = true)
			->and($adapter->constant = function($constantName) use (& $atoumVersion, & $atoumDirectory) {
					switch ($constantName)
					{
						case atoum\runner::atoumVersionConstant:
							return $atoumVersion = uniqid();

						case atoum\runner::atoumDirectoryConstant:
							return $atoumDirectory = uniqid();
					}
				}
			)
			->and($runner = new atoum\runner())
			->and($runner->setPhp($php))
			->and($runner->setAdapter($adapter))
			->and($runner->setScore($score = new \mock\mageekguy\atoum\runner\score()))
			->then
				->object($runner->setPathAndVersionInScore())->isIdenticalTo($runner)
				->mock($score)
					->call('setAtoumVersion')->withArguments($atoumVersion)->once()
					->call('setAtoumPath')->withArguments($atoumDirectory)->once()
					->call('setPhpPath')->withArguments($phpPath)->once()
					->call('setPhpVersion')->withArguments($phpVersion)->once()
			->if($adapter->defined = false)
			->and($runner->setScore($score = new \mock\mageekguy\atoum\runner\score()))
			->then
				->object($runner->setPathAndVersionInScore())->isIdenticalTo($runner)
				->mock($score)
					->call('setAtoumVersion')->withArguments(null)->once()
					->call('setAtoumPath')->withArguments(null)->once()
					->call('setPhpPath')->withArguments($phpPath)->once()
					->call('setPhpVersion')->withArguments($phpVersion)->once()
			->if($this->calling($php)->getExitCode = rand(1, PHP_INT_MAX))
			->and($runner->setScore($score = new \mock\mageekguy\atoum\runner\score()))
			->then
				->exception(function() use ($runner) {
						$runner->setPathAndVersionInScore();
					}
				)
					->isInstanceOf('mageekguy\atoum\exceptions\runtime')
					->hasMessage('Unable to get PHP version from \'' . $phpPath . '\'')
		;
	}

	public function testGetCoverage()
	{
		$this
			->if($runner = new atoum\runner())
			->then
				->object($runner->getCoverage())->isIdenticalTo($runner->getScore()->getCoverage())
		;
	}
}
