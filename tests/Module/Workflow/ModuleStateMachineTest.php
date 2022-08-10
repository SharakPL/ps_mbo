<?php
/**
 * Copyright since 2007 PrestaShop SA and Contributors
 * PrestaShop is an International Registered Trademark & Property of PrestaShop SA
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License 3.0 (AFL-3.0)
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * @author    PrestaShop SA <contact@prestashop.com>
 * @copyright Since 2007 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License 3.0 (AFL-3.0)
 */

namespace PrestaShop\Module\Mbo\Tests\Module\Workflow;

use PHPUnit\Framework\TestCase;
use PrestaShop\Module\Mbo\Module\Module;
use PrestaShop\Module\Mbo\Module\TransitionModule;
use PrestaShop\Module\Mbo\Module\Workflow\Event\TransitionEventSubscriber;
use PrestaShop\Module\Mbo\Module\Workflow\ModuleStateMachine;
use PrestaShop\Module\Mbo\Module\Workflow\TransitionsManager;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\String\UnicodeString;
use Symfony\Component\Workflow\Transition;

class ModuleStateMachineTest extends TestCase
{
    private const TRANSITION_INSTALLED__ENABLED_MOBILE_DISABLED = [
        'name' => ModuleStateMachine::TRANSITION_INSTALLED__ENABLED_MOBILE_DISABLED,
        'froms' => [ModuleStateMachine::STATUS_INSTALLED],
        'tos' => [ModuleStateMachine::STATUS_ENABLED__MOBILE_DISABLED],
    ];
    private const TRANSITION_INSTALLED__DISABLED_MOBILE_ENABLED = [
        'name' => ModuleStateMachine::TRANSITION_INSTALLED__DISABLED_MOBILE_ENABLED,
        'froms' => [ModuleStateMachine::STATUS_INSTALLED],
        'tos' => [ModuleStateMachine::STATUS_DISABLED__MOBILE_ENABLED],
    ];
    private const TRANSITION_INSTALLED__RESET = [
        'name' => ModuleStateMachine::TRANSITION_INSTALLED__RESET,
        'froms' => [ModuleStateMachine::STATUS_INSTALLED],
        'tos' => [ModuleStateMachine::STATUS_RESET],
    ];
    private const TRANSITION_INSTALLED__CONFIGURED = [
        'name' => ModuleStateMachine::TRANSITION_INSTALLED__CONFIGURED,
        'froms' => [ModuleStateMachine::STATUS_INSTALLED],
        'tos' => [ModuleStateMachine::STATUS_CONFIGURED],
    ];
    private const TRANSITION_INSTALLED__UPGRADED = [
        'name' => ModuleStateMachine::TRANSITION_INSTALLED__UPGRADED,
        'froms' => [ModuleStateMachine::STATUS_INSTALLED],
        'tos' => [ModuleStateMachine::STATUS_UPGRADED],
    ];
    private const TRANSITION_INSTALLED__UNINSTALLED = [
        'name' => ModuleStateMachine::TRANSITION_INSTALLED__UNINSTALLED,
        'froms' => [ModuleStateMachine::STATUS_INSTALLED],
        'tos' => [ModuleStateMachine::STATUS_UNINSTALLED],
    ];

    private const TRANSITION_ENABLED_MOBILE_ENABLED__ENABLED_MOBILE_DISABLED = [
        'name' => ModuleStateMachine::TRANSITION_ENABLED_MOBILE_ENABLED__ENABLED_MOBILE_DISABLED,
        'froms' => [ModuleStateMachine::STATUS_ENABLED__MOBILE_ENABLED],
        'tos' => [ModuleStateMachine::STATUS_ENABLED__MOBILE_DISABLED],
    ];
    private const TRANSITION_ENABLED_MOBILE_ENABLED__DISABLED_MOBILE_ENABLED = [
        'name' => ModuleStateMachine::TRANSITION_ENABLED_MOBILE_ENABLED__DISABLED_MOBILE_ENABLED,
        'froms' => [ModuleStateMachine::STATUS_ENABLED__MOBILE_ENABLED],
        'tos' => [ModuleStateMachine::STATUS_DISABLED__MOBILE_ENABLED],
    ];
    private const TRANSITION_ENABLED_MOBILE_ENABLED__RESET = [
        'name' => ModuleStateMachine::TRANSITION_ENABLED_MOBILE_ENABLED__RESET,
        'froms' => [ModuleStateMachine::STATUS_ENABLED__MOBILE_ENABLED],
        'tos' => [ModuleStateMachine::STATUS_RESET],
    ];
    private const TRANSITION_ENABLED_MOBILE_ENABLED__UPGRADED = [
        'name' => ModuleStateMachine::TRANSITION_ENABLED_MOBILE_ENABLED__UPGRADED,
        'froms' => [ModuleStateMachine::STATUS_ENABLED__MOBILE_ENABLED],
        'tos' => [ModuleStateMachine::STATUS_UPGRADED],
    ];
    private const TRANSITION_ENABLED_MOBILE_ENABLED__CONFIGURED = [
        'name' => ModuleStateMachine::TRANSITION_ENABLED_MOBILE_ENABLED__CONFIGURED,
        'froms' => [ModuleStateMachine::STATUS_ENABLED__MOBILE_ENABLED],
        'tos' => [ModuleStateMachine::STATUS_CONFIGURED],
    ];
    private const TRANSITION_ENABLED_MOBILE_ENABLED__UNINSTALLED = [
        'name' => ModuleStateMachine::TRANSITION_ENABLED_MOBILE_ENABLED__UNINSTALLED,
        'froms' => [ModuleStateMachine::STATUS_ENABLED__MOBILE_ENABLED],
        'tos' => [ModuleStateMachine::STATUS_UNINSTALLED],
    ];

    private const TRANSITION_ENABLED_MOBILE_DISABLED__ENABLED_MOBILE_ENABLED = [
        'name' => ModuleStateMachine::TRANSITION_ENABLED_MOBILE_DISABLED__ENABLED_MOBILE_ENABLED,
        'froms' => [ModuleStateMachine::STATUS_ENABLED__MOBILE_DISABLED],
        'tos' => [ModuleStateMachine::STATUS_ENABLED__MOBILE_ENABLED],
    ];
    private const TRANSITION_ENABLED_MOBILE_DISABLED__INSTALLED = [
        'name' => ModuleStateMachine::TRANSITION_ENABLED_MOBILE_DISABLED__INSTALLED,
        'froms' => [ModuleStateMachine::STATUS_ENABLED__MOBILE_DISABLED],
        'tos' => [ModuleStateMachine::STATUS_INSTALLED],
    ];
    private const TRANSITION_ENABLED_MOBILE_DISABLED__RESET = [
        'name' => ModuleStateMachine::TRANSITION_ENABLED_MOBILE_DISABLED__RESET,
        'froms' => [ModuleStateMachine::STATUS_ENABLED__MOBILE_DISABLED],
        'tos' => [ModuleStateMachine::STATUS_RESET],
    ];
    private const TRANSITION_ENABLED_MOBILE_DISABLED__UPGRADED = [
        'name' => ModuleStateMachine::TRANSITION_ENABLED_MOBILE_DISABLED__UPGRADED,
        'froms' => [ModuleStateMachine::STATUS_ENABLED__MOBILE_DISABLED],
        'tos' => [ModuleStateMachine::STATUS_UPGRADED],
    ];
    private const TRANSITION_ENABLED_MOBILE_DISABLED__CONFIGURED = [
        'name' => ModuleStateMachine::TRANSITION_ENABLED_MOBILE_DISABLED__CONFIGURED,
        'froms' => [ModuleStateMachine::STATUS_ENABLED__MOBILE_DISABLED],
        'tos' => [ModuleStateMachine::STATUS_CONFIGURED],
    ];
    private const TRANSITION_ENABLED_MOBILE_DISABLED__UNINSTALLED = [
        'name' => ModuleStateMachine::TRANSITION_ENABLED_MOBILE_DISABLED__UNINSTALLED,
        'froms' => [ModuleStateMachine::STATUS_ENABLED__MOBILE_DISABLED],
        'tos' => [ModuleStateMachine::STATUS_UNINSTALLED],
    ];

    private const TRANSITION_DISABLED_MOBILE_ENABLED__INSTALLED = [
        'name' => ModuleStateMachine::TRANSITION_DISABLED_MOBILE_ENABLED__INSTALLED,
        'froms' => [ModuleStateMachine::STATUS_DISABLED__MOBILE_ENABLED],
        'tos' => [ModuleStateMachine::STATUS_INSTALLED],
    ];
    private const TRANSITION_DISABLED_MOBILE_ENABLED__ENABLED_MOBILE_ENABLED = [
        'name' => ModuleStateMachine::TRANSITION_DISABLED_MOBILE_ENABLED__ENABLED_MOBILE_ENABLED,
        'froms' => [ModuleStateMachine::STATUS_DISABLED__MOBILE_ENABLED],
        'tos' => [ModuleStateMachine::STATUS_ENABLED__MOBILE_ENABLED],
    ];
    private const TRANSITION_DISABLED_MOBILE_ENABLED__RESET = [
        'name' => ModuleStateMachine::TRANSITION_DISABLED_MOBILE_ENABLED__RESET,
        'froms' => [ModuleStateMachine::STATUS_DISABLED__MOBILE_ENABLED],
        'tos' => [ModuleStateMachine::STATUS_RESET],
    ];
    private const TRANSITION_DISABLED_MOBILE_ENABLED__UPGRADED = [
        'name' => ModuleStateMachine::TRANSITION_DISABLED_MOBILE_ENABLED__UPGRADED,
        'froms' => [ModuleStateMachine::STATUS_DISABLED__MOBILE_ENABLED],
        'tos' => [ModuleStateMachine::STATUS_UPGRADED],
    ];
    private const TRANSITION_DISABLED_MOBILE_ENABLED__CONFIGURED = [
        'name' => ModuleStateMachine::TRANSITION_DISABLED_MOBILE_ENABLED__CONFIGURED,
        'froms' => [ModuleStateMachine::STATUS_DISABLED__MOBILE_ENABLED],
        'tos' => [ModuleStateMachine::STATUS_CONFIGURED],
    ];
    private const TRANSITION_DISABLED_MOBILE_ENABLED__UNINSTALLED = [
        'name' => ModuleStateMachine::TRANSITION_DISABLED_MOBILE_ENABLED__UNINSTALLED,
        'froms' => [ModuleStateMachine::STATUS_DISABLED__MOBILE_ENABLED],
        'tos' => [ModuleStateMachine::STATUS_UNINSTALLED],
    ];

    private const TRANSITION_UNINSTALLED__INSTALLED = [
        'name' => ModuleStateMachine::TRANSITION_UNINSTALLED__INSTALLED,
        'froms' => [ModuleStateMachine::STATUS_UNINSTALLED],
        'tos' => [ModuleStateMachine::STATUS_INSTALLED],
    ];

    /**
     * @var ModuleStateMachine
     */
    protected $moduleStateMachine;

    protected function setUp(): void
    {
        $eventDispatcher = new EventDispatcher();
        $this->moduleStateMachine = new ModuleStateMachine($eventDispatcher);
    }

    public function testModuleUninstalledPossibleTransitions()
    {
        $module = $this->getTransitionModule('x_module', '1.0.0', false, false, false);

        $possibleTransitions = $this->moduleStateMachine->getEnabledTransitions($module);

        $this->assertSame(ModuleStateMachine::STATUS_UNINSTALLED, $module->getStatus());
        $this->assertSame([
            self::TRANSITION_UNINSTALLED__INSTALLED,
        ], $this->transitionsToArray($possibleTransitions));
    }

    public function testModuleInstalledPossibleTransitions()
    {
        $module = $this->getTransitionModule('x_module', '1.0.0', true, false, false);

        $possibleTransitions = $this->moduleStateMachine->getEnabledTransitions($module);

        $this->assertSame(ModuleStateMachine::STATUS_INSTALLED, $module->getStatus());
        $this->assertSame([
            self::TRANSITION_INSTALLED__ENABLED_MOBILE_DISABLED,
            self::TRANSITION_INSTALLED__DISABLED_MOBILE_ENABLED,
            self::TRANSITION_INSTALLED__CONFIGURED,
            self::TRANSITION_INSTALLED__RESET,
            self::TRANSITION_INSTALLED__UPGRADED,
            self::TRANSITION_INSTALLED__UNINSTALLED,
        ], $this->transitionsToArray($possibleTransitions));
    }

    public function testModuleEnabledMobileDisabledPossibleTransitions()
    {
        $module = $this->getTransitionModule('x_module', '1.0.0', true, false, true);

        $possibleTransitions = $this->moduleStateMachine->getEnabledTransitions($module);

        $this->assertSame(ModuleStateMachine::STATUS_ENABLED__MOBILE_DISABLED, $module->getStatus());
        $this->assertSame([
            self::TRANSITION_ENABLED_MOBILE_DISABLED__INSTALLED,
            self::TRANSITION_ENABLED_MOBILE_DISABLED__ENABLED_MOBILE_ENABLED,
            self::TRANSITION_ENABLED_MOBILE_DISABLED__CONFIGURED,
            self::TRANSITION_ENABLED_MOBILE_DISABLED__RESET,
            self::TRANSITION_ENABLED_MOBILE_DISABLED__UPGRADED,
            self::TRANSITION_ENABLED_MOBILE_DISABLED__UNINSTALLED,
        ], $this->transitionsToArray($possibleTransitions));
    }

    public function testModuleEnabledMobileEnabledPossibleTransitions()
    {
        $module = $this->getTransitionModule('x_module', '1.0.0', true, true, true);

        $possibleTransitions = $this->moduleStateMachine->getEnabledTransitions($module);

        $this->assertSame(ModuleStateMachine::STATUS_ENABLED__MOBILE_ENABLED, $module->getStatus());
        $this->assertSame([
            self::TRANSITION_ENABLED_MOBILE_ENABLED__ENABLED_MOBILE_DISABLED,
            self::TRANSITION_ENABLED_MOBILE_ENABLED__DISABLED_MOBILE_ENABLED,
            self::TRANSITION_ENABLED_MOBILE_ENABLED__CONFIGURED,
            self::TRANSITION_ENABLED_MOBILE_ENABLED__RESET,
            self::TRANSITION_ENABLED_MOBILE_ENABLED__UPGRADED,
            self::TRANSITION_ENABLED_MOBILE_ENABLED__UNINSTALLED,
        ], $this->transitionsToArray($possibleTransitions));
    }

    public function testModuleDisabledMobileEnabledPossibleTransitions()
    {
        $module = $this->getTransitionModule('x_module', '1.0.0', true, true, false);

        $possibleTransitions = $this->moduleStateMachine->getEnabledTransitions($module);

        $this->assertSame(ModuleStateMachine::STATUS_DISABLED__MOBILE_ENABLED, $module->getStatus());
        $this->assertSame([
            self::TRANSITION_DISABLED_MOBILE_ENABLED__INSTALLED,
            self::TRANSITION_DISABLED_MOBILE_ENABLED__ENABLED_MOBILE_ENABLED,
            self::TRANSITION_DISABLED_MOBILE_ENABLED__CONFIGURED,
            self::TRANSITION_DISABLED_MOBILE_ENABLED__RESET,
            self::TRANSITION_DISABLED_MOBILE_ENABLED__UPGRADED,
            self::TRANSITION_DISABLED_MOBILE_ENABLED__UNINSTALLED,
        ], $this->transitionsToArray($possibleTransitions));
    }

    /**
     * @dataProvider getModuleAttributesAndAppliedTransitions
     */
    public function testApplyTransitions(array $moduleAttributes, string $targetStatus, string $transitionName)
    {
        $module = $this->getTransitionModule(
            $moduleAttributes['name'],
            $moduleAttributes['version'],
            $moduleAttributes['installed'],
            $moduleAttributes['active_on_mobile'],
            $moduleAttributes['active']
        );

        $transitionsManager = $this->createMock(TransitionsManager::class);
        $methodName = (new UnicodeString($transitionName))->camel()->toString();
        $transitionsManager
            ->expects($this->once())
            ->method($methodName)
            ->with($module, $targetStatus, [
                'transitionsManager' => $transitionsManager,
                'method' => $methodName,
            ])
        ;
        $eventDispatcher = new EventDispatcher();
        $eventDispatcher->addSubscriber(new TransitionEventSubscriber($transitionsManager));
        $moduleStateMachine = new ModuleStateMachine($eventDispatcher);

        $this->assertTrue($moduleStateMachine->can($module, $transitionName));

        $moduleStateMachine->apply($module, $transitionName);
    }

    /**
     * @dataProvider getModuleAttributesAndAppliedTransitions
     */
    public function testGetTransition(array $moduleAttributes, string $targetStatus, string $transitionName)
    {
        $module = $this->getTransitionModule(
            $moduleAttributes['name'],
            $moduleAttributes['version'],
            $moduleAttributes['installed'],
            $moduleAttributes['active_on_mobile'],
            $moduleAttributes['active']
        );

        $this->assertSame($transitionName, $this->moduleStateMachine->getTransition($module, $targetStatus));
    }

    public function getModuleAttributesAndAppliedTransitions()
    {
        yield [
            ['name' => 'x_module', 'version' => '1.0.0', 'installed' => true, 'active' => false, 'active_on_mobile' => false], //module attributes
            ModuleStateMachine::STATUS_ENABLED__MOBILE_DISABLED, //targeted status
            ModuleStateMachine::TRANSITION_INSTALLED__ENABLED_MOBILE_DISABLED, //transitionName
        ];

        yield [
            ['name' => 'x_module', 'version' => '1.0.0', 'installed' => true, 'active' => false, 'active_on_mobile' => false], //module attributes
            ModuleStateMachine::STATUS_DISABLED__MOBILE_ENABLED, //targeted status
            ModuleStateMachine::TRANSITION_INSTALLED__DISABLED_MOBILE_ENABLED, //transitionName
        ];

        yield [
            ['name' => 'x_module', 'version' => '1.0.0', 'installed' => true, 'active' => false, 'active_on_mobile' => false], //module attributes
            ModuleStateMachine::STATUS_RESET, //targeted status
            ModuleStateMachine::TRANSITION_INSTALLED__RESET, //transitionName
        ];

        yield [
            ['name' => 'x_module', 'version' => '1.0.0', 'installed' => true, 'active' => false, 'active_on_mobile' => false], //module attributes
            ModuleStateMachine::STATUS_CONFIGURED, //targeted status
            ModuleStateMachine::TRANSITION_INSTALLED__CONFIGURED, //transitionName
        ];

        yield [
            ['name' => 'x_module', 'version' => '1.0.0', 'installed' => true, 'active' => false, 'active_on_mobile' => false], //module attributes
            ModuleStateMachine::STATUS_UPGRADED, //targeted status
            ModuleStateMachine::TRANSITION_INSTALLED__UPGRADED, //transitionName
        ];

        yield [
            ['name' => 'x_module', 'version' => '1.0.0', 'installed' => true, 'active' => false, 'active_on_mobile' => false], //module attributes
            ModuleStateMachine::STATUS_UNINSTALLED, //targeted status
            ModuleStateMachine::TRANSITION_INSTALLED__UNINSTALLED, //transitionName
        ];

        yield [
            ['name' => 'x_module', 'version' => '1.0.0', 'installed' => true, 'active' => true, 'active_on_mobile' => true], //module attributes
            ModuleStateMachine::STATUS_ENABLED__MOBILE_DISABLED, //targeted status
            ModuleStateMachine::TRANSITION_ENABLED_MOBILE_ENABLED__ENABLED_MOBILE_DISABLED, //transitionName
        ];

        yield [
            ['name' => 'x_module', 'version' => '1.0.0', 'installed' => true, 'active' => true, 'active_on_mobile' => true], //module attributes
            ModuleStateMachine::STATUS_DISABLED__MOBILE_ENABLED, //targeted status
            ModuleStateMachine::TRANSITION_ENABLED_MOBILE_ENABLED__DISABLED_MOBILE_ENABLED, //transitionName
        ];

        yield [
            ['name' => 'x_module', 'version' => '1.0.0', 'installed' => true, 'active' => true, 'active_on_mobile' => true], //module attributes
            ModuleStateMachine::STATUS_RESET, //targeted status
            ModuleStateMachine::TRANSITION_ENABLED_MOBILE_ENABLED__RESET, //transitionName
        ];

        yield [
            ['name' => 'x_module', 'version' => '1.0.0', 'installed' => true, 'active' => true, 'active_on_mobile' => true], //module attributes
            ModuleStateMachine::STATUS_UPGRADED, //targeted status
            ModuleStateMachine::TRANSITION_ENABLED_MOBILE_ENABLED__UPGRADED, //transitionName
        ];

        yield [
            ['name' => 'x_module', 'version' => '1.0.0', 'installed' => true, 'active' => true, 'active_on_mobile' => true], //module attributes
            ModuleStateMachine::STATUS_CONFIGURED, //targeted status
            ModuleStateMachine::TRANSITION_ENABLED_MOBILE_ENABLED__CONFIGURED, //transitionName
        ];

        yield [
            ['name' => 'x_module', 'version' => '1.0.0', 'installed' => true, 'active' => true, 'active_on_mobile' => true], //module attributes
            ModuleStateMachine::STATUS_UNINSTALLED, //targeted status
            ModuleStateMachine::TRANSITION_ENABLED_MOBILE_ENABLED__UNINSTALLED, //transitionName
        ];

        yield [
            ['name' => 'x_module', 'version' => '1.0.0', 'installed' => true, 'active' => true, 'active_on_mobile' => false], //module attributes
            ModuleStateMachine::STATUS_ENABLED__MOBILE_ENABLED, //targeted status
            ModuleStateMachine::TRANSITION_ENABLED_MOBILE_DISABLED__ENABLED_MOBILE_ENABLED, //transitionName
        ];

        yield [
            ['name' => 'x_module', 'version' => '1.0.0', 'installed' => true, 'active' => true, 'active_on_mobile' => false], //module attributes
            ModuleStateMachine::STATUS_INSTALLED, //targeted status
            ModuleStateMachine::TRANSITION_ENABLED_MOBILE_DISABLED__INSTALLED, //transitionName
        ];

        yield [
            ['name' => 'x_module', 'version' => '1.0.0', 'installed' => true, 'active' => true, 'active_on_mobile' => false], //module attributes
            ModuleStateMachine::STATUS_RESET, //targeted status
            ModuleStateMachine::TRANSITION_ENABLED_MOBILE_DISABLED__RESET, //transitionName
        ];

        yield [
            ['name' => 'x_module', 'version' => '1.0.0', 'installed' => true, 'active' => true, 'active_on_mobile' => false], //module attributes
            ModuleStateMachine::STATUS_UPGRADED, //targeted status
            ModuleStateMachine::TRANSITION_ENABLED_MOBILE_DISABLED__UPGRADED, //transitionName
        ];

        yield [
            ['name' => 'x_module', 'version' => '1.0.0', 'installed' => true, 'active' => true, 'active_on_mobile' => false], //module attributes
            ModuleStateMachine::STATUS_CONFIGURED, //targeted status
            ModuleStateMachine::TRANSITION_ENABLED_MOBILE_DISABLED__CONFIGURED, //transitionName
        ];

        yield [
            ['name' => 'x_module', 'version' => '1.0.0', 'installed' => true, 'active' => true, 'active_on_mobile' => false], //module attributes
            ModuleStateMachine::STATUS_UNINSTALLED, //targeted status
            ModuleStateMachine::TRANSITION_ENABLED_MOBILE_DISABLED__UNINSTALLED, //transitionName
        ];

        yield [
            ['name' => 'x_module', 'version' => '1.0.0', 'installed' => true, 'active' => false, 'active_on_mobile' => true], //module attributes
            ModuleStateMachine::STATUS_INSTALLED, //targeted status
            ModuleStateMachine::TRANSITION_DISABLED_MOBILE_ENABLED__INSTALLED, //transitionName
        ];

        yield [
            ['name' => 'x_module', 'version' => '1.0.0', 'installed' => true, 'active' => false, 'active_on_mobile' => true], //module attributes
            ModuleStateMachine::STATUS_ENABLED__MOBILE_ENABLED, //targeted status
            ModuleStateMachine::TRANSITION_DISABLED_MOBILE_ENABLED__ENABLED_MOBILE_ENABLED, //transitionName
        ];

        yield [
            ['name' => 'x_module', 'version' => '1.0.0', 'installed' => true, 'active' => false, 'active_on_mobile' => true], //module attributes
            ModuleStateMachine::STATUS_RESET, //targeted status
            ModuleStateMachine::TRANSITION_DISABLED_MOBILE_ENABLED__RESET, //transitionName
        ];

        yield [
            ['name' => 'x_module', 'version' => '1.0.0', 'installed' => true, 'active' => false, 'active_on_mobile' => true], //module attributes
            ModuleStateMachine::STATUS_UPGRADED, //targeted status
            ModuleStateMachine::TRANSITION_DISABLED_MOBILE_ENABLED__UPGRADED, //transitionName
        ];

        yield [
            ['name' => 'x_module', 'version' => '1.0.0', 'installed' => true, 'active' => false, 'active_on_mobile' => true], //module attributes
            ModuleStateMachine::STATUS_CONFIGURED, //targeted status
            ModuleStateMachine::TRANSITION_DISABLED_MOBILE_ENABLED__CONFIGURED, //transitionName
        ];

        yield [
            ['name' => 'x_module', 'version' => '1.0.0', 'installed' => true, 'active' => false, 'active_on_mobile' => true], //module attributes
            ModuleStateMachine::STATUS_UNINSTALLED, //targeted status
            ModuleStateMachine::TRANSITION_DISABLED_MOBILE_ENABLED__UNINSTALLED, //transitionName
        ];

        yield [
            ['name' => 'x_module', 'version' => '1.0.0', 'installed' => false, 'active' => false, 'active_on_mobile' => false], //module attributes
            ModuleStateMachine::STATUS_INSTALLED, //targeted status
            ModuleStateMachine::TRANSITION_UNINSTALLED__INSTALLED, //transitionName
        ];
    }

    private function getTransitionModule(
        string $name,
        string $version,
        bool $installed,
        bool $activeOnMobile,
        bool $active
    ): TransitionModule {
        return new TransitionModule($name, $version, $installed, $activeOnMobile, $active);
    }

    private function transitionsToArray(array $transitions): array
    {
        $convertedTransitions = [];

        /** @var Transition $transition */
        foreach ($transitions as $transition) {
            $convertedTransitions[] = [
                'name' => $transition->getName(),
                'froms' => $transition->getFroms(),
                'tos' => $transition->getTos(),
            ];
        }

        return $convertedTransitions;
    }
}
