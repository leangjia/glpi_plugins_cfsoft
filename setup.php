<?php
/*
 * @version $Id: setup.php 313 2018-02-10 08:52:58Z liangjia $
 -------------------------------------------------------------------------
 cfsoft - Cfsoft Report&Print plugin for GLPI
 Copyright (C) 2018-2118 by the cfsoft Development Team.

 http://khsoft.com
 -------------------------------------------------------------------------

 LICENSE

 This file is part of cfsoft.

 cfsoft is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 cfsoft is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with cfsoft. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 */

/**
 * Init the hooks of the plugins -Needed
 **/
function plugin_init_cfsoft() {
   global $PLUGIN_HOOKS, $CFG_GLPI;

   $PLUGIN_HOOKS['csrf_compliant']['cfsoft'] = true;

   Plugin::registerClass('PluginCfsoftPreference',
                         array('addtabon' => array('Preference')));

   Plugin::registerClass('PluginCfsoftProfile',
                         array('addtabon' => array('Profile')));

   $PLUGIN_HOOKS['change_profile']['cfsoft'] = array('PluginCfsoftProfile','changeprofile');

   if (isset($_SESSION["glpi_plugin_cfsoft_profile"])
       && $_SESSION["glpi_plugin_cfsoft_profile"]["cfsoft"]) {

      $PLUGIN_HOOKS['menu_toadd']['cfsoft']['tools'] = 'PluginCfsoftPreference';

      $PLUGIN_HOOKS['pre_item_purge']['cfsoft'] = array('Profile' => array('PluginCfsoftProfile',
                                                                             'cleanProfiles'));

      $PLUGIN_HOOKS['change_entity']['cfsoft'] = 'plugin_change_entity_Cfsoft';

      if (isset($_SESSION["glpi_plugin_cfsoft_loaded"])
          && $_SESSION["glpi_plugin_cfsoft_loaded"] == 1
          && class_exists('PluginCfsoftConfig')) {

         foreach (PluginCfsoftConfig::getTypes() as $type) {
            $PLUGIN_HOOKS['item_update']['cfsoft'][$type]  = 'plugin_item_update_cfsoft';
            $PLUGIN_HOOKS['item_delete']['cfsoft'][$type]  = 'plugin_cfsoft_reload';
            $PLUGIN_HOOKS['item_restore']['cfsoft'][$type] = 'plugin_cfsoft_reload';
         }
      }

      if ($_SERVER['PHP_SELF'] == $CFG_GLPI["root_doc"]."/front/central.php"
          && (!isset($_SESSION["glpi_plugin_cfsoft_loaded"])
              || $_SESSION["glpi_plugin_cfsoft_loaded"] == 0)
          && isset($_SESSION["glpi_plugin_cfsoft_preference"])
          && $_SESSION["glpi_plugin_cfsoft_preference"] == 1) {

            Html::redirect($CFG_GLPI["root_doc"]."/plugins/cfsoft/index.php");
      }

      if ($_SERVER['PHP_SELF'] == $CFG_GLPI["root_doc"]."/front/logout.php"
          && (isset($_SESSION["glpi_plugin_cfsoft_loaded"])
          && $_SESSION["glpi_plugin_cfsoft_loaded"] == 1
          && class_exists('PluginCfsoftConfig'))) {

         $config = new PluginCfsoftConfig();
         $config->hideCfsoft();
      }
      // Add specific files to add to the header : javascript or css
      $PLUGIN_HOOKS['add_javascript']['cfsoft']  = "dtree.js";
      $PLUGIN_HOOKS['add_css']['cfsoft']         = "dtree.css";
      $PLUGIN_HOOKS['add_javascript']['cfsoft']  = "functions.js";
      $PLUGIN_HOOKS['add_css']['cfsoft']         = "style.css";
      $PLUGIN_HOOKS['add_javascript']['cfsoft']  = "cfsoft.js";
      $PLUGIN_HOOKS['add_css']['cfsoft']         = "cfsoft.css";
   }

   // Config page
   if (Session::haveRight("config", UPDATE) || Session::haveRight("profile", UPDATE)) {
      $PLUGIN_HOOKS['config_page']['cfsoft'] = 'front/config.form.php';
   }
}


/**
 * Get the name and the version of the plugin - Needed
**/
function plugin_version_cfsoft() {

   return array('name'           => __('康虎报表打印插件forGLPI', '康虎云报表'),
                'version'        => '0.0.1',
                'license'        => 'GPLv2+',
                'author'         => '康虎云报表群583804904',
                'homepage'       => 'https://github.com/leangjia/glpi_plugins_cfsoft',
                'minGlpiVersion' => '0.84'); // For compatibility / no install in version < 0.78
}


function plugin_cfsoft_check_prerequisites() {
   if (version_compare(GLPI_VERSION, '9.1', 'lt') || version_compare(GLPI_VERSION, '9.2', 'ge')) {
      echo 'This plugin requires GLPI >= 9.1';
      return false;
   }
   return true;
}


function plugin_cfsoft_check_config() {
   return true;
}