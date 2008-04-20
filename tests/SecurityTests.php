<?php

require_once dirname(dirname(__FILE__)).'/DwooCompiler.php';

function testphpfunc($input) { return $input.'OK'; }

class SecurityTests extends PHPUnit_Framework_TestCase
{
	protected $compiler;
	protected $dwoo;
	protected $policy;

	public function __construct()
	{
		$this->compiler = new DwooCompiler();
		$this->dwoo = new Dwoo();
		$this->policy = new DwooSecurityPolicy();
		$this->dwoo->setSecurityPolicy($this->policy);
	}

    public function testConstantHandling()
    {
    	$tpl = new DwooTemplateString('{$dwoo.const.DWOO_DIRECTORY}');
		$tpl->forceCompilation();

		$this->assertEquals("", $this->dwoo->get($tpl, array(), $this->compiler));

		$this->policy->setConstantHandling(DwooSecurityPolicy::CONST_ALLOW);

    	$tpl = new DwooTemplateString('{$dwoo.const.DWOO_DIRECTORY}');
		$tpl->forceCompilation();

		$this->assertEquals(DWOO_DIRECTORY, $this->dwoo->get($tpl, array(), $this->compiler));
    }

    public function testPhpHandling()
    {
		$this->policy->setPhpHandling(DwooSecurityPolicy::PHP_ALLOW);

    	$tpl = new DwooTemplateString('<?php echo "moo"; ?>');
		$tpl->forceCompilation();

		$this->assertEquals("moo", $this->dwoo->get($tpl, array(), $this->compiler));


		$this->policy->setPhpHandling(DwooSecurityPolicy::PHP_ENCODE);

    	$tpl = new DwooTemplateString('<?php echo "moo"; ?>');
		$tpl->forceCompilation();

		$this->assertEquals(htmlspecialchars('<?php echo "moo"; ?>'), $this->dwoo->get($tpl, array(), $this->compiler));


		$this->policy->setPhpHandling(DwooSecurityPolicy::PHP_REMOVE);

    	$tpl = new DwooTemplateString('<?php echo "moo"; ?>');
		$tpl->forceCompilation();

		$this->assertEquals('', $this->dwoo->get($tpl, array(), $this->compiler));
    }

    public function testAllowPhpFunction()
    {
		$this->policy->allowPhpFunction('testphpfunc');

    	$tpl = new DwooTemplateString('{testphpfunc("foo")}');
		$tpl->forceCompilation();

		$this->assertEquals("fooOK", $this->dwoo->get($tpl, array(), $this->compiler));
    }

    public function testAllowDirectoryGetSet()
    {
    	$old = $this->policy->getAllowedDirectories();
		$this->policy->allowDirectory(array('./resources'));
		$this->policy->allowDirectory('./temp');
		$this->assertEquals(array_merge($old, array(realpath('./resources')=>true, realpath('./temp')=>true)), $this->policy->getAllowedDirectories());

    	$this->policy->disallowDirectory(array('./resources'));
		$this->policy->disallowDirectory('./temp');
		$this->assertEquals($old, $this->policy->getAllowedDirectories());
    }

    public function testAllowPhpGetSet()
    {
    	$old = $this->policy->getAllowedPhpFunctions();
		$this->policy->allowPhpFunction(array('a','b'));
		$this->policy->allowPhpFunction('c');
		$this->assertEquals(array_merge($old, array('a'=>true, 'b'=>true, 'c'=>true)), $this->policy->getAllowedPhpFunctions());

		$this->policy->disallowPhpFunction(array('a', 'b'));
		$this->policy->disallowPhpFunction('c');
		$this->assertEquals($old, $this->policy->getAllowedPhpFunctions());
    }
}

?>