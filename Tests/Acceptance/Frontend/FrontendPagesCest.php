<?php
declare(strict_types = 1);

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
class FrontendPagesCest
{
    /**
     * @param AcceptanceTester $I
     */
    public function save(AcceptanceTester $I)
    {
        $I->amOnPage('/');
        $I->see('About Cookies');
        $I->click('[data-cookieman-save]');
        $I->dontSee('About Cookies');
        $I->seeCookie('CookieConsent');
        $I->assertEquals('mandatory', $I->grabCookie('CookieConsent'));
    }

    /**
     * @param AcceptanceTester $I
     */
    public function saveAll(AcceptanceTester $I)
    {
        $I->amOnPage('/');
        $I->see('About Cookies');
        $I->click('[data-cookieman-accept-all]');
        $I->dontSee('About Cookies');
        $I->seeCookie('CookieConsent');
        $I->assertEquals('mandatory|marketing', $I->grabCookie('CookieConsent'));
    }

    /**
     * @param AcceptanceTester $I
     */
    public function selectGroupAndSave(AcceptanceTester $I)
    {
        $I->amOnPage('/');
        $I->see('About Cookies');
        $I->click('Settings');
        $I->see('Marketing');
        $I->click('Marketing');
        $I->checkOption('[name=marketing]');
        $I->see('_gat');
        $I->click('Save');
        $I->dontSee('About Cookies');
        $I->seeCookie('CookieConsent');
        $I->assertEquals('mandatory|marketing', $I->grabCookie('CookieConsent'));
    }

    /**
     * @param AcceptanceTester $I
     */
    public function reopenAndRevoke(AcceptanceTester $I)
    {
        $I->setCookie('CookieConsent', 'mandatory|marketing');
        $I->amOnPage('/');
        $I->dontSee('About Cookies');
        $I->executeJS('cookieman.showOnce()');
        $I->dontSee('About Cookies');
        $I->executeJS('cookieman.show()');
        $I->see('About Cookies');
        $I->click('Settings');
        $I->see('Marketing');
        $I->click('Marketing');
        $I->canSeeCheckboxIsChecked('[name=marketing]');
        $I->uncheckOption('[name=marketing]');
        $I->click('Save');
        $I->dontSee('About Cookies');
        $I->seeCookie('CookieConsent');
        $I->assertEquals('mandatory', $I->grabCookie('CookieConsent'));
    }
}
