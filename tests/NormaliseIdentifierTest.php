<?php

namespace Chenos\V8Js\ModuleLoader\Tests;

use PHPUnit\Framework\TestCase;
use Chenos\V8Js\ModuleLoader\ModuleLoader;
use Chenos\V8Js\ModuleLoader\FileNotFoundException;

class NormaliseIdentifierTest extends TestCase
{
    /**
     * @group normal
     */
    public function testNormal()
    {
        $loader = $this->newModuleLoader([
            '/app/foo/bar/main.js' => '',
        ]);

        $this->assertExamples($loader, [
            ['', './foo/bar/main', '/app/foo/bar', 'main.js'],
            ['./foo', './bar/main', '/app/foo/bar', 'main.js'],
            ['./foo/bar', './main', '/app/foo/bar', 'main.js'],
            ['/app', './foo/bar/main', '/app/foo/bar', 'main.js'],
            ['/app/foo', './bar/main', '/app/foo/bar', 'main.js'],
            ['/app/foo/bar', './main', '/app/foo/bar', 'main.js'],
        ]);

        $this->assertExamples($loader, [
            ['', './foo/bar/main.js', '/app/foo/bar', 'main.js'],
            ['./foo', './bar/main.js', '/app/foo/bar', 'main.js'],
            ['./foo/bar', './main.js', '/app/foo/bar', 'main.js'],
            ['/app', './foo/bar/main.js', '/app/foo/bar', 'main.js'],
            ['/app/foo', './bar/main.js', '/app/foo/bar', 'main.js'],
            ['/app/foo/bar', './main.js', '/app/foo/bar', 'main.js'],
        ]);
    }

    /**
     * @group index
     */
    public function testIndex()
    {
        $loader = $this->newModuleLoader([
            '/app/foo/bar/main' => false,
            '/app/foo/bar/main/index.js' => '',
        ]);

        // $loader->addOverride('vue', 'vue/dist/vue');

        $this->assertExamples($loader, [
            ['', './foo/bar/main', '/app/foo/bar/main', 'index.js'],
            ['./foo', './bar/main', '/app/foo/bar/main', 'index.js'],
            ['./foo/bar', './main', '/app/foo/bar/main', 'index.js'],
            ['/app', './foo/bar/main', '/app/foo/bar/main', 'index.js'],
            ['/app/foo', './bar/main', '/app/foo/bar/main', 'index.js'],
            ['/app/foo/bar', './main', '/app/foo/bar/main', 'index.js'],
        ]);
    }

    /**
     * @group packagejson
     */
    public function testPackageJson()
    {
        $loader = $this->newModuleLoader([
            '/app/foo/bar/main' => false,
            '/app/foo/bar/main/dist/foo.js' => '',
            '/app/foo/bar/main/package.json' => '{"main": "dist/foo.js"}',
        ]);

        $this->assertExamples($loader, [
            ['', './foo/bar/main', '/app/foo/bar/main/dist', 'foo.js'],
            ['./foo', './bar/main', '/app/foo/bar/main/dist', 'foo.js'],
            ['./foo/bar', './main', '/app/foo/bar/main/dist', 'foo.js'],
            ['/app', './foo/bar/main', '/app/foo/bar/main/dist', 'foo.js'],
            ['/app/foo', './bar/main', '/app/foo/bar/main/dist', 'foo.js'],
            ['/app/foo/bar', './main', '/app/foo/bar/main/dist', 'foo.js'],
        ]);
    }

    /**
     * @group priority
     */
    public function testPriority1()
    {
        $loader = $this->newModuleLoader([
            '/app/foo/bar/main' => false,
            '/app/foo/bar/main.js' => '',
            '/app/foo/bar/main/index.js' => '',
            '/app/foo/bar/main/dist/foo.js' => '',
            '/app/foo/bar/main/package.json' => '{"main": "dist/foo.js"}',
        ]);

        $this->assertExamples($loader, [
            ['', './foo/bar/main', '/app/foo/bar/main/dist', 'foo.js'],
            ['./foo', './bar/main', '/app/foo/bar/main/dist', 'foo.js'],
            ['./foo/bar', './main', '/app/foo/bar/main/dist', 'foo.js'],
            ['/app', './foo/bar/main', '/app/foo/bar/main/dist', 'foo.js'],
            ['/app/foo', './bar/main', '/app/foo/bar/main/dist', 'foo.js'],
            ['/app/foo/bar', './main', '/app/foo/bar/main/dist', 'foo.js'],
        ]);
    }

    /**
     * @group priority
     */
    public function testPriority2()
    {
        $loader = $this->newModuleLoader([
            '/app/foo/bar/main' => false,
            '/app/foo/bar/main.js' => '',
            '/app/foo/bar/main/index.js' => '',
        ]);

        $this->assertExamples($loader, [
            ['', './foo/bar/main', '/app/foo/bar/main', 'index.js'],
            ['./foo', './bar/main', '/app/foo/bar/main', 'index.js'],
            ['./foo/bar', './main', '/app/foo/bar/main', 'index.js'],
            ['/app', './foo/bar/main', '/app/foo/bar/main', 'index.js'],
            ['/app/foo', './bar/main', '/app/foo/bar/main', 'index.js'],
            ['/app/foo/bar', './main', '/app/foo/bar/main', 'index.js'],
        ]);
    }

    /**
     * @group module
     */
    public function testModules1()
    {
        $loader = $this->newModuleLoader([
            '/node_modules/vue/index.js' => ''
        ]);

        $this->assertExamples($loader, [
            ['', 'vue', '/node_modules/vue', 'index.js'],
            ['./foo', 'vue', '/node_modules/vue', 'index.js'],
            ['./foo/bar', 'vue', '/node_modules/vue', 'index.js'],
            ['/app/foo', 'vue', '/node_modules/vue', 'index.js'],
            ['/app/foo/bar', 'vue', '/node_modules/vue', 'index.js'],
        ]);
    }

    /**
     * @group override
     */
    public function testModuleOverride1()
    {
        $loader = $this->newModuleLoader([
            '/node_modules/vue/index.js' => '',
            '/node_modules/vue/dist/vue.js' => '',
        ]);

        $loader->addOverride('vue', 'vue/dist/vue');

        $this->assertExamples($loader, [
            ['', 'vue', '/node_modules/vue/dist', 'vue.js'],
            ['./foo', 'vue', '/node_modules/vue/dist', 'vue.js'],
            ['./foo/bar', 'vue', '/node_modules/vue/dist', 'vue.js'],
            ['/app/foo', 'vue', '/node_modules/vue/dist', 'vue.js'],
            ['/app/foo/bar', 'vue', '/node_modules/vue/dist', 'vue.js'],
        ]);
    }

    /**
     * @group override
     */
    public function testModuleOverrideWithArrayArgs()
    {
        $loader = $this->newModuleLoader([
            '/node_modules/vue/index.js' => '',
            '/node_modules/vue/dist/vue.js' => '',
        ]);

        $loader->addOverride(['vue' => 'vue/dist/vue']);

        $this->assertExamples($loader, [
            ['', 'vue', '/node_modules/vue/dist', 'vue.js'],
            ['./foo', 'vue', '/node_modules/vue/dist', 'vue.js'],
            ['./foo/bar', 'vue', '/node_modules/vue/dist', 'vue.js'],
            ['/app/foo', 'vue', '/node_modules/vue/dist', 'vue.js'],
            ['/app/foo/bar', 'vue', '/node_modules/vue/dist', 'vue.js'],
        ]);
    }

    /**
     * @group override
     */
    public function testModuleOverride2()
    {
        $loader = $this->newModuleLoader([
            '/node_modules/vue/index.js' => '',
        ]);

        $module = new \stdClass;

        $loader->addOverride('vue', $module);

        $this->assertExamples($loader, [
            ['', 'vue', '', 'vue'],
            ['./foo', 'vue', '', 'vue'],
            ['./foo/bar', 'vue', '', 'vue'],
            ['/app/foo', 'vue', '', 'vue'],
            ['/app/foo/bar', 'vue', '', 'vue'],
        ]);

        $this->assertSame($module, $loader->loadModule('vue'));
    }

    /**
     * @group override
     */
    public function testModuleOverride3()
    {
        $loader = $this->newModuleLoader([
            '/node_modules/vue/index.js' => '',
        ]);

        $module = new \stdClass;

        $loader->addOverride('vue', $module);

        $this->assertExamples($loader, [
            ['', 'vue', '', 'vue'],
            ['./foo', 'vue', '', 'vue'],
            ['./foo/bar', 'vue', '', 'vue'],
            ['/app/foo', 'vue', '', 'vue'],
            ['/app/foo/bar', 'vue', '', 'vue'],
        ]);

        $this->assertSame($module, $loader->loadModule('vue'));
    }

    /**
     * @group extensions
     */
    public function testModuleExtensions()
    {
        $loader = $this->newModuleLoader([
            '/app/components/app.vue' => '',
            '/app/components/app.js' => '',
        ]);

        $loader->setExtensions('.vue', '.js');

        $this->assertExamples($loader, [
            ['', './components/app', '/app/components', 'app.vue'],
            ['./components', './app', '/app/components', 'app.vue'],
            ['', '/app/components/app', '/app/components', 'app.vue'],
            ['/app/components', './app', '/app/components', 'app.vue'],
        ]);
    }

    /**
     * @group ModulesDirectory
     */
    public function testModulesDirectory()
    {
        $loader = $this->newModuleLoader([
            '/bower_components/vue/index.js' => ''
        ], [
            '/node_modules',
            '/bower_components',
        ]);

        $this->assertExamples($loader, [
            ['', 'vue', '/bower_components/vue', 'index.js'],
            ['./foo', 'vue', '/bower_components/vue', 'index.js'],
            ['./foo/bar', 'vue', '/bower_components/vue', 'index.js'],
            ['/app/foo', 'vue', '/bower_components/vue', 'index.js'],
            ['/app/foo/bar', 'vue', '/bower_components/vue', 'index.js'],
        ]);
    }

    /**
     * @group ModulesDirectory
     */
    public function testModules()
    {
        $loader = $this->newModuleLoader([]);

        $this->assertExamples($loader, [
            ['', 'vue', '', 'vue'],
            ['./foo', 'vue', '', 'vue'],
            ['./foo/bar', 'vue', '', 'vue'],
            ['/app/foo', 'vue', '', 'vue'],
            ['/app/foo/bar', 'vue', '', 'vue'],
        ]);
    }

    public function testLoadModule()
    {
        $loader = $this->newModuleLoader([
            '/app/foo.js' => 'foo',
            '/app/bar.js' => 'bar',
        ]);

        $this->assertEquals('foo', $loader->loadModule('/app/foo.js'));
        $this->assertEquals('foo', $loader->loadModule('./foo.js', false));
        $this->assertEquals('bar', $loader->loadModule('/app/bar.js'));
        $this->assertEquals('bar', $loader->loadModule('./bar.js', false));
    }

    public function testLoadModuleException()
    {
        $loader = $this->newModuleLoader([
            '/app/foo.js' => 'foo',
            '/app/bar.js' => 'bar',
        ]);

        $this->expectException(FileNotFoundException::class);
        $this->expectExceptionMessage("'./baz.js' module does not exists.");

        $this->assertFalse($loader->loadModule('./baz.js', false));
    }

    public function testException()
    {
        $this->expectException(FileNotFoundException::class);
        $this->expectExceptionMessage("'./app' module does not exists.");
        $loader = $this->newModuleLoader([]);
        $loader->normaliseIdentifier('', './app');
    }

    public function testSetEntryDirectory()
    {
        $loader = new ModuleLoader('/app1');
        $loader->setEntryDirectory('/app2');
        $this->assertAttributeEquals('/app2', 'entryDir', $loader);
    }

    public function testSetFileSystem()
    {
        $loader = new ModuleLoader('/app1');
        $loader->setFileSystem($fs = new FileSystem);
        $this->assertAttributeEquals($fs, 'fs', $loader);
    }

    protected function newModuleLoader($paths, $vendorDir = ['/node_modules'], $entryDir = '/app')
    {
        $loader = new ModuleLoader($entryDir, new FileSystem($paths));
        $loader->addVendorDirectory(...$vendorDir);

        return $loader;
    }

    protected function assertExamples($loader, $examples)
    {
        foreach ($examples as $values) {
            $this->assertEquals($loader->normaliseIdentifier(
                array_shift($values), array_shift($values)), $values);
        }
    }
}

