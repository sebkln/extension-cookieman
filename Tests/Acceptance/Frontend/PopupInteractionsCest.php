<?php
declare(strict_types=1);

/*
 * This file is part of the package dmind/cookieman.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace Dmind\Cookieman\Tests\Acceptance\Frontend;

/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

use Dmind\Cookieman\Tests\Acceptance\Support\AcceptanceTester;

/**
 * Tests clicking through some frontend pages
 */
class PopupInteractionsCest
{
    const PATH_ROOT = '/';
    const PATH_2NDPAGE = '/customize';
    const PATH_3RDPAGE = '/?id=10';
    const PATH_4THPAGE = '/pages';
    const PATH_5THPAGE = '/content-examples';

    const MODAL_TITLE_EN = 'About Cookies';

    const SELECTOR_BUTTON_SAVE_NOT_SAVEALL = '[data-cookieman-save]:not([data-cookieman-accept-all])';
    const SELECTOR_BUTTON_SAVEALL = '[data-cookieman-accept-all]';
    const SETTINGS_LINK_TEXT = 'Settings';
    const BUTTON_TITLE_SAVE = 'Save';

    const COOKIENAME = 'CookieConsent';
    const COOKIE_VALUE_SEPARATOR = '|';

    const BS_PACKAGE_MENUITEM_SELECTOR = '[href$="/pages"],[href$="?id=66"]';
    const BS_PACKAGE_SUBMENUITEM_TEXT = '2 Columns 50/50';
    // for introduction-package ^3.0
    const BS_PACKAGE_INTRO3_MENUITEM_SELECTOR = '[href$="?id=51"]';
    const BS_PACKAGE_INTRO3_SUBMENUITEM_TEXT = 'Form elements';

    const JS_SHOW_COOKIEMAN = 'cookieman.show()';
    const JS_SHOWONCE_COOKIEMAN = 'cookieman.showOnce()';
    const JS_HIDE_COOKIEMAN = 'cookieman.hide()';
    const JS_ONSCRIPTLOADED_COOKIEMAN = "
            cookieman.onScriptLoaded(
                arguments[0],
                arguments[1],
                function (trackingObjectKey, scriptId) {
                    alert(arguments[0] + ':' + arguments[1] + ' loaded')
                }
            );
        ";

    const GROUP_KEY_MANDATORY = 'mandatory';

    const GROUP_KEY_2ND = 'marketing';
    const GROUP_TITLE_2ND = 'Marketing';
    const COOKIE_TITLE_IN_2ND_GROUP = '_gat';

    const GROUP_KEY_TESTGROUP = 'testgroup';
    const TRACKINGOBJECT_IN_TESTGROUP_WITH_2SCRIPTS = 'Crowdin';

    /**
     * @param AcceptanceTester $I
     */
    public function doesNotBreakBootstrapPackage(AcceptanceTester $I)
    {
        $I->amOnPage(self::PATH_ROOT);
        $I->wait(0.5);
        $I->see(self::MODAL_TITLE_EN);
        $I->executeJS(self::JS_HIDE_COOKIEMAN);
        $I->wait(0.5);
        $I->dontSee(self::MODAL_TITLE_EN);
        if ($I->tryToMoveMouseOver(self::BS_PACKAGE_MENUITEM_SELECTOR)) { // hover over menu
            $I->see(self::BS_PACKAGE_SUBMENUITEM_TEXT);
        } else { // introduction-package ^3.0
            $I->moveMouseOver(self::BS_PACKAGE_INTRO3_MENUITEM_SELECTOR);
            $I->see(self::BS_PACKAGE_INTRO3_SUBMENUITEM_TEXT);
        }
    }

    /**
     * @param AcceptanceTester $I
     */
    public function save(AcceptanceTester $I)
    {
        $I->amOnPage(self::PATH_ROOT);
        $I->wait(0.5);
        $I->see(self::MODAL_TITLE_EN);
        $I->click(self::SELECTOR_BUTTON_SAVE_NOT_SAVEALL);
        $I->wait(0.5);
        $I->dontSee(self::MODAL_TITLE_EN);
        $I->seeCookie(self::COOKIENAME);
        $I->assertEquals(
            self::GROUP_KEY_MANDATORY,
            $I->grabCookie(self::COOKIENAME, ['path' => self::PATH_ROOT])
        );
    }

    /**
     * @param AcceptanceTester $I
     */
    public function saveAll(AcceptanceTester $I)
    {
        $I->amOnPage(self::PATH_2NDPAGE);
        $I->wait(0.5);
        $I->see(self::MODAL_TITLE_EN);
        $I->tryToClick(self::SETTINGS_LINK_TEXT); // customtheme doesn't have an accordion
        $I->click(self::SELECTOR_BUTTON_SAVEALL);
        $I->wait(0.5);
        $I->dontSee(self::MODAL_TITLE_EN);
        $I->seeCookie(self::COOKIENAME);
        $I->assertStringStartsWith(
            $this->cookieValueForGroups([self::GROUP_KEY_MANDATORY, self::GROUP_KEY_2ND]),
            $I->grabCookie(self::COOKIENAME, ['path' => self::PATH_ROOT])
        );
    }

    /**
     * @param AcceptanceTester $I
     */
    public function notShownOnImprint(AcceptanceTester $I)
    {
        $I->amOnPage(self::PATH_3RDPAGE);
        $I->wait(0.5);
        $I->dontSee(self::MODAL_TITLE_EN);
    }

    /**
     * @param AcceptanceTester $I
     */
    public function selectGroupAndSaveMobile(AcceptanceTester $I)
    {
        $I->amOnPage(self::PATH_4THPAGE);
        $I->resizeWindow(480, 800);
        $I->wait(0.5);
        $I->see(self::MODAL_TITLE_EN);
        if ($I->tryToClick(self::SETTINGS_LINK_TEXT)) {
            $I->wait(0.5);
        }
        $I->see(self::GROUP_TITLE_2ND);
        $I->tryToClick(self::GROUP_TITLE_2ND);
        $I->wait(0.5);
        $I->see(self::COOKIE_TITLE_IN_2ND_GROUP); // a single row in the table
        if (!$I->tryToCheckOption('[name=' . self::GROUP_KEY_2ND . ']')) { // theme: *-modal
            $I->executeJS('$("[name=' . self::GROUP_KEY_2ND . ']").click()'); // theme: bootstrap3-banner
        }
        $I->seeCheckboxIsChecked('[name=' . self::GROUP_KEY_2ND . ']');
        $I->click(self::BUTTON_TITLE_SAVE);
        $I->wait(0.5);
        $I->dontSee(self::MODAL_TITLE_EN);
        $I->seeCookie(self::COOKIENAME);
        $I->assertEquals(
            $this->cookieValueForGroups([self::GROUP_KEY_MANDATORY, self::GROUP_KEY_2ND]),
            $I->grabCookie(self::COOKIENAME, ['path' => self::PATH_ROOT])
        );
    }

    /**
     * @param AcceptanceTester $I
     */
    public function reopenAndRevoke(AcceptanceTester $I)
    {
        $I->amOnPage(self::PATH_4THPAGE);
        $I->setCookie(
            self::COOKIENAME,
            $this->cookieValueForGroups([self::GROUP_KEY_MANDATORY, self::GROUP_KEY_2ND]),
            ['path' => self::PATH_ROOT]
        );
        $I->amOnPage(self::PATH_5THPAGE);
        $I->wait(0.5);
        $I->dontSee(self::MODAL_TITLE_EN);
        $I->executeJS(self::JS_SHOWONCE_COOKIEMAN);
        $I->wait(0.5);
        $I->dontSee(self::MODAL_TITLE_EN);
        $I->executeJS(self::JS_SHOW_COOKIEMAN);
        $I->wait(0.5);
        $I->see(self::MODAL_TITLE_EN);
        $I->tryToClick(self::SETTINGS_LINK_TEXT);
        $I->wait(0.5);
        $I->see(self::GROUP_TITLE_2ND);
        $I->tryToClick(self::GROUP_TITLE_2ND);
        $I->wait(0.5);
        $I->seeCheckboxIsChecked('[name=' . self::GROUP_KEY_2ND . ']');
        if (!$I->tryToUncheckOption('[name=' . self::GROUP_KEY_2ND . ']')) { // theme: *-modal
            $I->executeJS('$("[name=' . self::GROUP_KEY_2ND . ']").click()'); // theme: bootstrap3-banner
        }
        $I->dontSeeCheckboxIsChecked('[name=' . self::GROUP_KEY_2ND . ']');
        $I->click('Save');
        $I->wait(0.5);
        $I->dontSee(self::MODAL_TITLE_EN);
        $I->seeCookie(self::COOKIENAME);
        $I->assertEquals(
            $this->cookieValueForGroups([self::GROUP_KEY_MANDATORY]),
            $I->grabCookie(self::COOKIENAME, ['path' => self::PATH_ROOT])
        );
    }

    /**
     * @param AcceptanceTester $I
     * @throws \Codeception\Exception\ModuleException
     */
    public function onScriptLoadedEventHandler(AcceptanceTester $I)
    {
        $I->amOnPage(self::PATH_ROOT);
        $I->setCookie(
            self::COOKIENAME,
            $this->cookieValueForGroups(
                [self::GROUP_KEY_MANDATORY, self::GROUP_KEY_TESTGROUP]
            ),
            ['path' => self::PATH_ROOT]
        );
        $I->amOnPage(self::PATH_ROOT);

        // test onScriptLoaded() callback
        $onScriptLoadedArgs = [self::TRACKINGOBJECT_IN_TESTGROUP_WITH_2SCRIPTS, 0];
        $I->executeJS(
            self::JS_ONSCRIPTLOADED_COOKIEMAN,
            $onScriptLoadedArgs
        );
        $I->wait(1);
        $I->seeInPopup($onScriptLoadedArgs[0] . ':' . $onScriptLoadedArgs[1] . ' loaded');
        $I->acceptPopup();

        // test onScriptLoaded() callback (when already loaded)
        $onScriptLoadedArgs = [self::TRACKINGOBJECT_IN_TESTGROUP_WITH_2SCRIPTS, 1];
        $I->executeJS(
            self::JS_ONSCRIPTLOADED_COOKIEMAN,
            $onScriptLoadedArgs
        );
        $I->wait(1);
        $I->seeInPopup($onScriptLoadedArgs[0] . ':' . $onScriptLoadedArgs[1] . ' loaded');
    }

    /**
     * @param array $groupKeys
     * @return string
     */
    protected function cookieValueForGroups(array $groupKeys)
    {
        return implode(self::COOKIE_VALUE_SEPARATOR, $groupKeys);
    }
}
