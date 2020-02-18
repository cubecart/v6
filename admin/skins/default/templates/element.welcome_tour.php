{*
 * CubeCart v6
 * ========================================
 * CubeCart is a registered trade mark of CubeCart Limited
 * Copyright CubeCart Limited 2017. All rights reserved.
 * UK Private Limited Company No. 5323904
 * ========================================
 * Web:   http://www.cubecart.com
 * Email:  sales@cubecart.com
 * License:  GPL-3.0 https://www.gnu.org/licenses/quick-guide-gplv3.html
 *}
 <ol id="joyrideTour" style="display: none">
		<li data-button="{$LANG.dashboard.ok_go}">
			<h2>{$LANG.common.hi_casual} {$ADMIN_USER_FIRST_NAME}!</h2>
			<p>{$LANG.dashboard.tour_welcome}</p>
		</li>
		<li data-id="nav_settings">
			<h2>1. {$LANG.navigation.nav_settings_store}</h2>
			<p>{$LANG.dashboard.tour_1}</p>
		</li>
		<li data-id="nav_categories">
			<h2>2. {$LANG.navigation.nav_categories}</h2>
			<p>{$LANG.dashboard.tour_2}</p>
		</li>
		<li data-id="nav_products">
			<h2>3. {$LANG.navigation.nav_products}</h2>
			<p>{$LANG.dashboard.tour_3}</p>
		</li>
		<li data-id="nav_plugins">
			<h2>4. {$LANG.navigation.nav_plugins}</h2>
			<p>{$LANG.dashboard.tour_5}</p>
		</li>
		<li data-id="nav_marketplace">
			<h2>5. {$LANG.navigation.nav_marketplace}</h2>
			<p>{$LANG.dashboard.tour_4}</p>
		</li>
		<li data-id="nav_docs">
		<h2>6. {$LANG.dashboard.tour_6_title}</h2>
			<p>{$LANG.dashboard.tour_6}</p>
		</li>
		<li data-id="clear_cache_master" class="cache">
			<h2>7. {$LANG.dashboard.tour_7_title}</h2>
			<p>{$LANG.dashboard.tour_7}</p>
		</li>
		<li data-id="help_menu" class="help">
			<h2>8. {$LANG.dashboard.tour_8_title}</h2>
			<p>{$LANG.dashboard.tour_8}</p>
		</li>
		<li class="joyride_tour_end" data-button="{$LANG.common.close}">
			<h2>{$LANG.common.thats_it}</h2>
			{$LANG.dashboard.tour_end}
		</li>
</ol>