<?php
class ProsulumMabAdmin{
	
	var $_data_RegisteredActionBoxes = array();
	
	var $_optin_Providers = array();
	var $_optin_AweberApplicationId = '60e2f3cd';
	var $_optin_AweberAuthenticationUrl = 'https://auth.aweber.com/1.0/oauth/authorize_app/';
	var $_optin_AweberFormActionUrl = 'http://www.aweber.com/scripts/addlead.pl';
	var $_optin_AweberListsTransient = '_mab_aweber_lists_transient';
	var $_optin_ConstantContactKey = '07671de3-6060-4ef8-b431-f1f48f8de026';
	var $_optin_MailChimpListsTransient = 'mab_mailchimp_lists_transient';

	var $_optin_SendReachListsTransient = 'mab_sendreach_lists_transient';

	var $_option_SettingsTransient = '_mab_settings_transient';
	var $_option_CurrentVersion = '_mab_current_version';
	
	var $_regex_Form = '/(<form\b[^>]*>)(.*?)(<\/form>)/ims';

	function __construct(){
		$this->add_actions();
		$this->add_filters();
		
		$this->initialize();
		$this->initializeActionBoxes();
	}
	
	function add_actions(){
		add_action('admin_head', array(&$this, 'processDuplicateDisplay'), 100);
		
		add_action( 'admin_menu', array( &$this, 'addAdminInterface' ) );
		add_action( 'admin_menu', array(&$this, 'rearrangeAdminInterfaceItems'), 1010);
		add_action( 'admin_init', array(&$this, 'processSubmissions' ) );
		add_action( 'save_post', array( &$this, 'saveActionBoxMeta' ), 10, 2 );
		add_action( 'manage_posts_custom_column', array( &$this, 'outputLandingPageTypeColumn' ), 10, 2 );
		
		add_action( 'wp_ajax_mab_optin_get_lists', array( &$this, 'ajaxOptinGetLists' ) );
		add_action( 'wp_ajax_mab_optin_process_manual_code', array( &$this, 'ajaxProcessOptinCode' ) );
		
		//add Magic Action Box metabox to post content types
		add_action( 'add_meta_boxes', array( &$this, 'addMetaBoxToOtherContentTypes') );

		//set callback function for handling setting up the action box type post
		add_action('mab_set_action_box_type', array( $this, 'setActionBoxType'), 10, 2 );

		/**
		 * Hacky part followed from Premise. This is to make sure people
		 * select an Action Box type before an Action Box type is actually created.
		 * Requirements as of WP3.3: Custom post type needs to have support for comments
		 * for this hack to work
		 */
		add_action('admin_notices', array( &$this, 'possiblyStartOutputBuffering' ) );
		add_filter('wp_comment_reply', array( &$this, 'possiblyEndOutputBUffering' ) );
		
	}
	
	function add_filters(){
		global $MabBase, $MabButton;
		add_filter( 'manage_edit-' . $MabBase->get_post_type() . '_columns', array( &$this, 'addColumnHeaderForpageType' ) );
		add_filter( 'pre_update_option_' . $MabButton->_option_ButtonSettings, array( &$this, 'writeConfiguredButtonsStylesheet' ), 10, 2 );
	}
	
	function initialize(){
		$currentVersion = get_option($this->_option_CurrentVersion);
		if($currentVersion != MAB_VERSION) {
			
			update_option($this->_option_CurrentVersion, MAB_VERSION);
		}

		$this->initializeOptinProviders();

		do_action('mab_admin_init');
	}
	
	function initializeActionBoxes(){
		//don't pass anything to get all types
		$actionBoxTypes = ProsulumMabCommon::getActionBoxTypes();
		
		foreach( $actionBoxTypes as $key => $box ){
			$this->addActionBoxType( $box['type'], $box['name'], $box['description'] );
		}

		//TODO: add a way to add new box types using actions/filters? Must be able to check
		//that the displayed Action Boxes in the post screen is valid though.
	}
	
	function addActionBoxType( $type, $name, $description ){
		if( !isset( $this->_data_RegisteredActionBoxes[ $type ] ) ){
			$this->_data_RegisteredActionBoxes[$type] = array(
				'type' => $type,
				'name' => $name,
				'description' => $description
			);
		}
	}
	
	function addAdminInterface(){
		global $MabButton, $MabBase;
		
		$hooks = array( 'post-new.php', 'post.php' );
		
		## MAIN SETTINGS
		$hooks[] = add_menu_page( __('Magic Action Box', 'mab'), __('Magic Action Box', 'mab'), 'manage_options', 'mab-main', array( &$this, 'displaySettingsPage' ), MAB_ASSETS_URL . 'images/cube.png', '66.5' );
		## MAIN SETTINGS
		$hooks[] = add_submenu_page( 'mab-main', __('Main Settings', 'mab' ), __('Main Settings', 'mab' ), 'manage_options', 'mab-main', array( &$this, 'displaySettingsPage' ) );

		## ACTION BOXES
		$hooks[] = add_submenu_page( 'mab-main', __('Action Boxes','mab'), __('Action Boxes','mab'), 'manage_options', 'edit.php?post_type=' . $MabBase->get_post_type() );
		
		$hooks[] = add_submenu_page( 'mab-main', __('New Action Box','mab'), __('New Action Box','mab'), 'manage_options', 'post-new.php?post_type=' . $MabBase->get_post_type() );

		## ACTION BOX SETTINGS
		//$hooks[] = add_submenu_page( 'mab-main', __('Action Box Settings', 'mab' ), __('Action Box Settings', 'mab' ), 'manage_options', 'mab-actionbox-settings', array( &$this, 'displayActionBoxSettingsPage' ) );
		
		## DESIGN
		
		$hooks[] = add_submenu_page( 'mab-main', __('Styles &amp; Buttons', 'mab' ), __('Styles &amp; Buttons', 'mab' ), 'manage_options', 'mab-design', array( &$this, 'displayDesignsPage' ) );
		
		## ADD/EDIT DESIGN/STYLE
		$styleTitle = __( 'Add Style', 'mab' );
		if( isset( $_GET['page'] ) && 'mab-style-settings' == $_GET['page'] && isset ( $_GET['mab-style-key'] )  && $this->isValidStyleKey( $_GET['mab-style-key'] ) ){
			$styleTitle = __('Edit Style', 'mab' );
		}
		//TODO: Rename $styleTitle when editing a style
		$hooks[] = add_submenu_page( 'mab-main', $styleTitle, $styleTitle, 'manage_options', 'mab-style-settings', array( &$this, 'displayStyleSettingsPage' ) );
		
		## ADD/EDIT BUTTONS
		$buttonTitle = __( 'Add Button', 'mab' );
		if( isset( $_GET['page'] ) && 'mab-button-settings' == $_GET['page'] && isset( $_GET['mab-button-id'] ) && $this->isValidButtonKey( $_GET['mab-button-id'] ) ){
			$buttonTitle = __('Edit Button', 'mab' );
		}
		
		$hooks[] = add_submenu_page( 'mab-main', $buttonTitle , $buttonTitle , 'manage_options', 'mab-button-settings', array( &$this, 'displayButtonSettingsPage' ) );

		$hooks[] = add_submenu_page( 'mab-main', __('Support', MAB_DOMAIN ), __('Support &amp; Links', MAB_DOMAIN), 'manage_options', 'mab-support', array( &$this, 'displaySupportPage' ) );
		
		$mab_hooks = apply_filters( 'mab_add_submenu_filter', $hooks );
		
		## ATTACH ASSETS
		foreach( $mab_hooks as $hook ){
			add_action("admin_print_styles-{$hook}", array( &$this, 'enqueueStylesForAdminPages' ) );
			add_action("admin_print_scripts-{$hook}", array( &$this, 'enqueueScriptsForAdminPages' ) );
		}
	}
	
	function rearrangeAdminInterfaceItems(){
		global $menu;
		
		$menu['66.3'] = array( '', 'read', 'separator-mab', '' , 'wp-menu-separator' );
		//$menu['66.4'] = $menu[777];
		//unset( $menu[777] );
		$menu['66.9'] = array( '', 'read', 'separator-mab', '' , 'wp-menu-separator' );
	}
	
	function addColumnHeaderForpageType( $columns ){
		$new = array( 'action-box-type' => __( 'Action Box Type', 'mab' ) );
		$columns = array_slice($columns, 0, 2, true) + $new + array_slice($columns, 2, count($columns), true);
		
		$new_columns['cb'] = $columns['cb'];
		$new_columns['title'] = __('Action Box Title','mab');
		$new_columns['actionbox-id'] = __('ID #','mab');
		$new_columns['action-box-type'] = __('Action Box Type', 'mab' );
		$new_columns['date'] = __('Last Modified','mab');
		return $new_columns;
		
		return $columns;
	}
	
	function outputLandingPageTypeColumn( $column, $postId ){
		global $MabBase;
		
		if( $column == 'action-box-type' ){
			echo esc_html( $MabBase->get_actionbox_type( $postId ) );
		} elseif( $column == 'actionbox-id' ){
			echo $postId;
		}
	}
	
	/**
	 * SETTINGS
	 * =============================================== */
	function getSettings(){
		global $MabBase;
		return $MabBase->get_settings();
	}
	
	function saveSettings( $settings ){
		global $MabBase;
		$MabBase->update_settings( $settings );
	}
	
	//DISPLAY SETTINGS CALLBACKS
	function displaySettingsPage(){
		global $MabBase;
		
		$data = $this->getSettings();
		
		//get all created action boxes
		$actionBoxesObj = get_posts( array( 'numberposts' => -1, 'post_type' => $MabBase->get_post_type(), 'orderby' => 'title', 'order' =>'ASC' ) );
		
		//create actio box content type array
		$actionBoxes = array();
		$actionBoxes['none'] = 'None';
		$actionBoxes['default'] = 'Use Default';
		foreach( $actionBoxesObj as $aBox ){
			$actionBoxes[ $aBox->ID ] = $aBox->post_title;
		}
		
		//get all categories and store in array
		$categoriesObj = get_categories( array( 'hide_empty' => 0 ) );
		foreach( $categoriesObj as $cat ){
			$categories[ $cat->cat_ID ] = $cat;
		}
		
		//add other variables as keys to the data array
		$data['actionboxList'] = $actionBoxes;
		$data['categories'] = $categories;
		$data['_optin_AweberAuthenticationUrl'] = $this->_optin_AweberAuthenticationUrl;
		$data['_optin_AweberApplicationId'] = $this->_optin_AweberApplicationId;
		
		//get messages and add to data array
		$messages = get_transient( $this->_option_SettingsTransient );
		$data['messages'] = $messages;
		
		$filename = $this->getSettingsViewTemplate( 'main' );
		
		$settings_page = ProsulumMabCommon::getView( $filename, $data );
		
		echo $settings_page;
		
		//use a switch block here
		//include( MAB_VIEWS_DIR . 'settings/main.php' );
	}
	
	function displayActionBoxSettingsPage(){
		global $MabBase;
	}
	
	function displayButtonSettingsPage(){
		global $MabBase, $MabButton;
		
		$filename = $this->getSettingsViewTemplate( 'button-settings' );
		//$buttons = $MabButton->getSettings();
		
		if( isset( $_GET['mab-button-id'] ) && $this->isValidButtonKey( $_GET['mab-button-id'] ) ){
			$button = $MabButton->getButton( $_GET['mab-button-id'] );
			$action = 'edit';
			$key = $_GET['mab-button-id'];
		} else {
			$button = $MabButton->getDefaultSettings();
			$action = 'add';
		}
		
		if( isset( $_GET['reset'] ) && 'true' == $_GET['reset'] ){
			$button = $MabButton->getDefaultSettings();
		}
		
		$data['key'] = isset( $key ) ? $key : '';
		$data['button'] = $button;
		$data['action'] = $action;
		$data['button-code'] = $MabButton->getButtonCode( $button );
		$output = ProsulumMabCommon::getView( $filename, $data );
		echo $output;
	}
	
	function displayStyleSettingsPage(){
		global $MabBase, $MabDesign, $mabStyleKey;
		
		$data = array();
		$key = isset( $_GET['mab-style-key'] ) ? absint( $_GET['mab-style-key'] ) : null;
		$mabStyleKey = $key;
		
		if( $key !== null && $this->isValidStyleKey( $key ) ){
			//edit a style
			$style= $MabDesign->getConfiguredStyle( $key );
			$action = 'edit';
		} else {
			//add new style
			$style = $MabDesign->getDefaultSettings();
			$action = 'add';
		}
		
		//TODO: add reset?
		
		$data['key'] = $key;
		$data['settings'] = $style;
		$data['action'] = $action;
		
		$filename = $this->getSettingsViewTemplate( 'style-settings' );
		$output = ProsulumMabCommon::getView( $filename, $data );
		echo $output;
	}
	
	function displayDesignsPage(){
		global $MabBase, $MabButton, $MabDesign;
		
		$filename = $this->getSettingsViewTemplate( 'design' );
		
		$data = array();
		
		//prepare configured buttons
		$data['buttons'] = $MabButton->getSettings();
		//prepare configured styles
		$data['styles'] = $MabDesign->getStyleSettings();
		
		$output = ProsulumMabCommon::getView( $filename, $data );
		echo $output;
		
	}
	
	function displaySupportPage(){
		$filename = $this->getSettingsViewTemplate('support');
		
		$out = ProsulumMabCommon::getView( $filename );

		echo $out;
	}
	
	function getSettingsViewTemplate( $template = '' ){
		
		$filename = '';
		$setting_dir = 'settings/';
		
		switch( $template ){
			case 'main': 
				$filename = $setting_dir . 'main.php';
				break;
			case 'button-settings':
				$filename = $setting_dir . 'button-settings.php';
				break;
			case 'design':
				$filename = $setting_dir . 'design.php';
				break;
			case 'style-settings':
				$filename = $setting_dir . 'style-settings.php';
				break;
			case 'support':
				$filename = $setting_dir . 'support.php';
				break;
			default: break; //empty $filename
		}
		
		return $filename;
	}
	
	/* Add Metabox to other content types (Posts, Pages) */
	function addMetaBoxToOtherContentTypes(){
		global $MabBase;
		
		$content_types = $MabBase->get_allowed_content_types();
		
		foreach( $content_types as $content_type ){
			add_meta_box( 'mab-post-action-box', __('Magic Action Box', 'mab' ), 'ProsulumMabMetaboxes::postActionBox', $content_type, 'normal', 'high' ); 
		}
	}
	
	/* Save Action Box Meta */
	function saveActionBoxMeta( $postId, $postObj ){
		global $MabBase, $post;
		
		$wp_is_post_autosave = wp_is_post_autosave( $post );
		$wp_is_post_revision = wp_is_post_revision( $post );
		
		//ignore autosaves
		if( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) 
			return;
		
		//save action for Action Box post types
		if( ( false === $wp_is_post_autosave || $wp_is_post_revision ) && is_object( $post ) && $MabBase->is_mab_post_type( $post->post_type ) ){
			
			$actionBoxType = $MabBase->get_actionbox_type( $postId );

			$this->processActionBoxTypeSpecificMeta( $postId, $post );

			//process action box design meta
			$this->processActionBoxDesignMeta( $postId, $post );

			do_action( 'mab_save_action_box', $actionBoxType, $postId, $post );
			
		}
		
		/* save action for other content types */
		if( ( false === $wp_is_post_autosave || $wp_is_post_revision ) && is_object( $post ) && $MabBase->is_allowed_content_type( $post->post_type ) ){
			$this->processActionBoxMetaForOtherContentTypes( $postId, $post );
		}
		
	}
	
	/**
	 * STYLES Utility
	 *
	 * Deprecated since 2.1. Use ProsulumMabCommon::getActionBoxStyles() instead
	 */
	function getActionBoxStyles(){
		$styles = array(
			'user' => array( 'id' => 'user', 'name' => 'User Settings', 'description' => 'Create your own style.' ),
			'default' => array( 'id' => 'default', 'name' => 'Default', 'description' => 'Starter style for your action box.' ),
			'mab-clean' => array( 'id' => 'mab-clean', 'name' => 'Clean', 'description' => 'A clean style for your action boxes.' ),
			'mab-crosswalk' => array( 'id' => 'mab-crosswalk', 'name' => 'Crosswalk', 'description' => 'Beautiful style with a touch of yellow.' ),
			'dark' => array( 'id' => 'dark', 'name' => 'Dark', 'description' => '' ),
			'lastlight' => array( 'id' => 'lastlight', 'name' => 'Last Light', 'description' => '' ),
			'royalty' => array( 'id' => 'royalty', 'name' => 'Royalty', 'description' => ''),
			'pink' => array( 'id' => 'pink', 'name' => 'Pink', 'description' => ''),
			'none' => array( 'id' => 'none', 'name' => 'None', 'description' => 'Don\'t use any style designs. Useful if you wish to roll out your own design.')
		);
		
		return $styles;
	}
	
	function getActionBoxStyleThumb( $id ){
		$thumb = ProsulumMabCommon::getStyleResource( $id, 'thumb' );
		return $thumb;
	}
	
	/**
	 * Buttons Utility
	 */
	function isValidButtonKey( $key ){
		global $MabButton;
		$settings = $MabButton->getSettings();
		return isset( $settings[$key] );
	}
	
	function saveConfiguredButton( $buttonSettings, $key ){
		global $MabButton;
		$key = $MabButton->updateSettings( $buttonSettings, $key );
		return $key;
	}

	function duplicateButton( $key ){
		global $MabButton;
		return $MabButton->duplicateButton( $key );
	}
	
	function deleteConfiguredButton( $key ){
		global $MabButton;
		return $MabButton->deleteConfiguredButton( $key );
	}
	
	function writeConfiguredButtonsStylesheet( $newValue, $oldValue ){
		global $MabButton;
		return $MabButton->writeConfiguredButtonsStylesheet( $newValue, $oldValue );
	}
	
	function createPreconfiguredButtons(){
		global $MabButton;
		$MabButton->createPreconfiguredButtons();
	}
	
	/**
	 * STyles Utility
	 */
	function isValidStyleKey( $key ){
		global $MabDesign;
		$settings = $MabDesign->getStyleSettings();
		return isset( $settings[$key] );
	}
	function deleteConfiguredStyle( $key = null ){
		global $MabDesign;
		return $MabDesign->deleteConfiguredStyle( $key );
	}
	function getConfiguredStyle( $key = null ){
		global $MabDesign;
		return $MabDesign->getConfiguredStyle( $key );
	}
	function saveConfiguredStyle( $settings, $key ){
		global $MabDesign;
		$key = $MabDesign->updateStyleSettings( $settings, $key );
		return $key;
	}
	
	/**
	 * Processing 
	 * ==========================================*/

	/**
	 * Intercepts values in the administrative interface to do various actions such as:
	 * - Saving settings
	 * - setting the action box type for new action boxes
	 * @return void
	 */
	function processSubmissions(){
		
		//process selection of Action Box type
		if( isset( $_GET['action_box_set'] ) && $_GET['action_box_set'] == 1 && wp_verify_nonce( $_GET['_wpnonce'], 'action_box_set' ) ){
			
			/**
			 * TODO: do some security checks on the values
			 */
			//$this->setActionBoxType( $_GET['post'], $_GET['action_box_type'] ); //removed v2.9.3
			do_action( 'mab_set_action_box_type', $_GET['post'], $_GET['action_box_type'] );

			wp_redirect( get_edit_post_link( $_GET['post'], 'raw') );
			exit();
		}
		
		//process duplicating an action box
		if( isset( $_GET['mab-action'] ) && $_GET['mab-action'] == 'duplicate' && !empty( $_GET['post'] ) && check_admin_referer( 'mab-duplicate_action_box_nonce' ) ){
			$result = $this->duplicateActionBox( $_GET['post'] );
			$dup_additional = '';
			if( $result ){
				$dup_additional = '&mab-duplicate-id='.$result;
				//add_action('admin_notices', array( $this, 'notifyOfDuplicate' ) );
			}
			wp_redirect( admin_url( 'post.php?post=' . $_GET['post'] . '&action=edit') );
			exit();
		}
		
		//process saving of main settings
		if( isset( $_POST['save-mab-settings'] ) && wp_verify_nonce( $_POST['save-mab-settings-nonce'], 'save-mab-settings' ) ){
			$this->processMabSettings();
			wp_redirect( admin_url( 'admin.php?page='. $_GET['page'].'&updated=true&mab-settings-updated=true' ) );
			exit();
		} elseif( isset( $_GET['page'] ) && isset( $_GET['mab-clear-cache'] ) && 'mab-main'== $_GET['page'] && 'true' == $_GET['mab-clear-cache'] && check_admin_referer( 'mab-clear-cache' ) ){
			
			ProsulumMabCommon::clearCacheDir();
			
			//set transient. This is to temporarily store messages so that they can be shown
			set_transient( $this->_option_SettingsTransient, array( 'updates' => array( __('Cache cleared.', 'mab' ) ) ), 10 );

			wp_redirect( admin_url('admin.php?page=mab-main&clear-cache=true') );
			exit();
		}
		
		//process buttons
		if( ( isset( $_POST['save-button-settings'] ) || isset( $_POST['button-settings']['reset'] ) ) && wp_verify_nonce( $_POST['save-mab-button-settings-nonce'], 'save-mab-button-settings-nonce' ) ){
			$buttonMessage = $this->processButtonSettings();
			wp_redirect(admin_url('admin.php?page=' . $_GET['page'] . '&' . $buttonMessage));
			exit();
		} elseif( isset( $_GET['mab-button-id'] ) && isset( $_GET['mab-button-action'] ) ){

			$button_action = $_GET['mab-button-action'];
			$button_id = $_GET['mab-button-id'];

			//check button key if valid
			if( !$this->isValidButtonKey( $button_id ) ){
				//button id/key is NOT valid
				wp_redirect( admin_url('admin.php?page=mab-design&mab-invalid-button-id=true') );
				exit();
			}

			if( 'duplicate' == $button_action && check_admin_referer('mab-duplicate-button') ) {
				$duplicate_button_id = $this->duplicateButton( $button_id );
				wp_redirect( admin_url('admin.php?page=mab-design&mab-button-duplicated=' . $duplicate_button_id));
				exit();
			} elseif( 'delete' == $button_action && check_admin_referer('mab-delete-button') ){
				$this->deleteConfiguredButton( $_GET['mab-button-id'] );
				wp_redirect( admin_url('admin.php?page=mab-design&deleted=true') );
				exit();
			}
		} elseif( isset( $_GET['mab-create-preconfigured'] ) && $_GET['mab-create-preconfigured'] == 'true' && check_admin_referer( 'mab-create-preconfigured' ) ){
			$this->createPreconfiguredButtons();
			wp_redirect( admin_url('admin.php?page=mab-design&mab-preconfigured-buttons=true' ) );
			exit();
		}
		
		//process styles
		if( isset( $_POST['save-style-settings'] ) && wp_verify_nonce( $_POST['save-mab-style-settings-nonce'], 'save-mab-style-settings-nonce' ) ){
			// save/update style
			$styleKey = $this->processStyleSettings();
			wp_redirect( add_query_arg( array( 'page' => 'mab-style-settings', 'updated' => 'true', 'mab-style-key' => $styleKey ), admin_url( 'admin.php' ) ) );
			exit();
		} elseif( isset( $_GET['mab-style-key'] ) && isset( $_GET['mab-delete-style'] ) && $_GET['mab-delete-style'] == 'true' && check_admin_referer( 'mab-delete-style' ) ){
			$this->deleteConfiguredStyle( $_GET['mab-style-key'] );
			wp_redirect( admin_url( 'admin.php?page=mab-design&deleted=true') );
			exit();
		} elseif( isset( $_GET['mab-duplicate-style'] ) && $_GET['mab-duplicate-style'] == 'true' && $this->isValidStyleKey( $_GET['mab-style-key'] ) && check_admin_referer( 'mab-duplicate-style' ) ){
			$style = $this->getConfiguredStyle( $_GET['mab-style-key'] );
			$style['title'] .= (' - ' . __('Copy', 'mab' ) );
			$style['timesaved'] = current_time( 'timestamp' );
			$this->saveConfiguredStyle( $style, null );
			wp_redirect( admin_url( 'admin.php?page=mab-design&duplicated=true' ) );
			exit();
		}
	}
	
	/**
	 * Handle posted data and save settings for the Magic Action Box plugin
	 */
	function processMabSettings(){
		$data = stripslashes_deep( $_POST );
		$mab_data = $data['mab'];
		
		$mab_data['others']['reorder-content-filters'] = isset( $mab_data['others']['reorder-content-filters'] ) ? 1 : 0;
		
		//get old settings and merge with $mab to new array $settings
		$old_settings = $this->getSettings();
		$settings = array_merge( $old_settings, $mab_data );
		
		$errors = array();
		
		//process aweber
		$aweberSettingsChanged = false;
		if( $settings['optin']['aweber-authorization'] != $old_settings['optin']['aweber-authorization'] ){
			$aweberSettingsChanged = true;
			//validate authorization code
			$aweberCheck = $this->validateAweberAuthorizationCode( $settings['optin']['aweber-authorization'] );
			
			if( is_array( $aweberCheck ) && !isset( $aweberCheck['error'] ) ){ //do a check here later
				$settings['optin']['allowed']['aweber'] = 1;
				$settings['optin']['aweber-account-info'] = $aweberCheck;
				//get lists and store into transient
				//$settings['optin']['aweber-lists'] = $this->getAweberLists( true ); //force update
			} else {
				$settings['optin']['allowed']['aweber'] = 0;
				$errors[] = $aweberCheck['error'];
				$settings['optin']['aweber-authorization'] = '';
			}
		} else {
			$settings['optin']['aweber-account-info'] = $old_settings['optin']['aweber-account-info'];
			$settings['optin']['allowed']['aweber'] = $old_settings['optin']['allowed']['aweber'];
			$settings['optin']['aweber-lists'] = $old_settings['optin']['aweber-lists'];
		}
		
		//process mailchimp
		$mailChimpSettingsChanged = false;
		if($settings['optin']['mailchimp-api'] != $old_settings['optin']['mailchimp-api']) {
			$mailChimpSettingsChanged = true;
			$mcApiKey = $settings['optin']['mailchimp-api'];
			$mailchimpCheck = $this->validateMailChimpAPIKey( $mcApiKey );
			
			if( true === $mailchimpCheck ) {
				$settings['optin']['allowed']['mailchimp'] = 1;
				//$settings['optin']['mailchimp-account-info'] = $this->getMailChimpAccountInfo( $mcApiKey );
				//$settings['optin']['mailchimp-lists'] = $this->getMailChimpLists( true ); //force update
			} else {
				$settings['optin']['allowed']['mailchimp'] = 0;
				$errors[] = $mailchimpCheck['error'];
				$settings['optin']['mailchimp-api'] = '';
			}
		} else {
			$settings['optin']['allowed']['mailchimp'] = $old_settings['optin']['allowed']['mailchimp'];
			$settings['optin']['mailchimp-lists'] = $old_settings['optin']['mailchimp-lists'];
		}

		// process SendReach
		$sendReachSettingsChanged = false;
		$processSendReach = false;
		if( !empty($settings['optin']['sendreach']['key']) && !empty($settings['optin']['sendreach']['secret'])){
			// sendreach is filled out

			if(empty($old_settings['optin']['sendreach'])){
				// no previous sendreach settings
				$processSendReach = true;

			} else {
				// check if there is change between old and current settings

				foreach($settings['optin']['sendreach'] as $k => $v){
					if($settings['optin']['sendreach'][$k] != $old_settings['optin']['sendreach'][$k]){
						$processSendReach = true;
					}
				}
			}
		} else {
			// sendreach is not filled out or one of the required fields
			// is missing
			if( !empty($settings['optin']['sendreach']['key']) || !empty($settings['optin']['sendreach']['secret']) ){
				$errors[] = __('SendReach app key and secret are both required fields.');
			}
			$settings['optin']['sendreach']['key'] = '';
			$settings['optin']['sendreach']['secret'] = '';
			$settings['optin']['allowed']['sendreach'] = 0;
		}

		if($processSendReach){

			$srKey = trim($settings['optin']['sendreach']['key']);
			$srSecret = trim($settings['optin']['sendreach']['secret']);
			$sendReachCheck = $this->validateSendReachApi($srKey, $srSecret);

			if(true === $sendReachCheck){
				$settings['optin']['sendreach']['key'] = $srKey;
				$settings['optin']['sendreach']['secret'] = $srSecret;
				$settings['optin']['allowed']['sendreach'] = 1;
			} else {
				$errors[] = $sendReachCheck['error'];
				$settings['optin']['sendreach']['key'] = '';
				$settings['optin']['sendreach']['secret'] = '';
				$settings['optin']['allowed']['sendreach'] = 0;
			}
		}
		
		//process manual opt in form. Created for consistency.
		$settings['optin']['allowed']['manual'] = 1;


		/* TODO: Process Global ActionBox Setting */

		//save settings
		$this->saveSettings($settings);
		
		//set transient. This is to temporarily store messages so that they can be shown
		set_transient( $this->_option_SettingsTransient, array( 'updates' => array( __('Settings Saved.', 'mab' ) ), 'errors' => $errors ), 10 );
		
		//set optin lists
		#aweber
		if( $aweberSettingsChanged && $settings['optin']['allowed']['aweber'] == 1 ){
			//running the function also stores the list into a transient
			$settings['optin']['aweber-lists'] = $this->getAweberLists( true ); //force update
		}
		#mailchimp
		if( $mailChimpSettingsChanged && $settings['optin']['allowed']['mailchimp'] == 1 ){
			//running the function also stores the list into a transient
			$settings['optin']['mailchimp-lists'] = $this->getMailChimpLists( true ); //force update
		}
	}
	
	function processStyleSettings(){
		global $MabDesign;
		
		$data = stripslashes_deep( $_POST );
		$settings = $data['mab-design'];
		$key = isset( $data['mab-style-key'] ) ? $data['mab-style-key'] : '';
		
		if( empty( $settings ) ){
			$settings = $MabDesign->getDefaultSettings();
		}
		
		$key = $this->saveConfiguredStyle( $settings, $key );
		
		//create stylesheet
		$this->createStyleSheet( $key );
		
		return $key;
	}
	
	function processButtonSettings(){
		global $MabButton;
		
		$keyMessage = '';
		
		$data = stripslashes_deep( $_POST );
		$button = $data['button-settings'];
		$key = $data['mab-button-key'];
		
		if( isset( $button['reset'] ) ){
			$message = 'reset=true&button-title=' . $button['title'];
			if( $data['mab-button-settings-action'] == 'edit' && isset( $key ) ){
				$keyMessage = "mab-button-id={$key}&";
			}
		} else {
			
			if( empty( $button ) )
				$button = $MabButton->getDefaultSettings();
				
			$key = $this->saveConfiguredButton( $button, $key );
			
			$keyMessage = "mab-button-id={$key}&";
			$message = 'updated=true';
		}
		
		return $keyMessage . $message;

	}
	 
	function processActionBoxTypeSpecificMeta( $postId, $post ){
		global $MabBase;
		
		if( !$MabBase->is_mab_post_type( $post->post_type ) ){
			return;
		}
		
		$data = stripslashes_deep($_POST);
		
		/** TODO:
		-do nonce check
		- don't save if the same data?
		**/
		
		//main settings
		if( isset( $data['mab'] ) && is_array( $data['mab'] ) ){
			$mab = $data['mab'];
			
			//UPDATE: this is already set on interception screen
			//$this->setActionBoxType( $postId, 'optin' );
			
			$type = $this->getActionBoxType( $postId );
			
			// do stuff for salesbox
			if ( $type == 'sales-box' ){
				$mab['main-button-attributes'] = esc_attr( $mab['main-button-attributes'] );
			}

			//do stuff for action boxes with optin i.e. optin, sharebox
			if(!empty($mab['optin'])){

				//get selected email provider
				$emailProvider = $mab['optin-provider'];

				if( $emailProvider == 'mailchimp' ){
					//append additional list data to mailchimp info 
					//NOTE 12/09/11: this section no longer needed?
					$listData = $this->getMailChimpListSingle( $mab['optin']['mailchimp']['list'] );

					// Added line, from https://coderwall.com/p/goabcq
					// @referred by https://wordpress.org/support/topic/mailchimp-subscribe-url-causes-warnings-on-secure-https-sites?replies=2#post-6115249
					$listData['subscribe_url_long'] = preg_replace('#^[^:/.]*[:/]+#i', '//', $listData['subscribe_url_long']);

					$mab['optin']['mailchimp']['form-action-url'] = str_replace('subscribe', 'subscribe/post', $listData['subscribe_url_long']);
					
				} elseif( $emailProvider == 'aweber' ){
				
					$mab['optin']['aweber']['form-action-url'] = $this->getAweberActionUrl();
				
				}

				if(isset($mab['optin']['manual']['code'])){
					$mab['optin']['manual']['code'] = htmlspecialchars_decode($mab['optin']['manual']['code']);
				}
				if(isset($mab['optin']['manual']['processed'])){
					$mab['optin']['manual']['processed'] = htmlspecialchars_decode($mab['optin']['manual']['processed']);
				}	

			}

			
			$mab = apply_filters( 'mab_update_action_box_meta', $mab, $postId, $data );

			$MabBase->update_mab_meta( $postId, $mab );
		}//ENDIF
		
	}
	
	function processActionBoxDesignMeta( $postId, $post ){
		global $MabBase;
		
		if( !$MabBase->is_mab_post_type( $post->post_type ) ){
			return;
		}
		
		$data = stripslashes_deep($_POST);
		
		//Design Settings
		if( isset( $data['mab-design'] ) && is_array( $data['mab-design'] ) ){
			$design = $data['mab-design'];
			
			$MabBase->update_mab_meta( $postId, $design, 'design' );
		}
		
		//create stylesheets
		$custom = isset( $design['custom_css'] ) ? $design['custom_css'] : '';
		
		$this->createActionBoxStyleSheet( $postId );

	}
	
	function processActionBoxMetaForOtherContentTypes( $post_id, $post ){
		global $MabBase;
		
		if( !$MabBase->is_allowed_content_type( $post->post_type ) )
			return;
			
		$data = stripslashes_deep( $_POST );
		
		//TODO: do nonce check
		
		if( !empty($data['postmeta']) && is_array( $data['postmeta'] ) ){
			$postmeta = $data['postmeta'];
			
			$MabBase->update_mab_meta( $post_id, $postmeta, 'post' );
		}
	}
	
	function duplicateActionBox( $source_id ){
		global $MabBase;
		
		do_action('mab_pre_duplicate_action_box', $source_id);

		$source = get_post( $source_id );
		if( is_null( $source ) || empty( $source ) || !is_object( $source ) ){
			return false;
		}
		
		//make sure that this is an action box post type and that actionbox type has been set
		$source_type = $this->getActionBoxType( $source->ID );
		if( $source->post_type != $MabBase->get_post_type() || empty( $source_type ) )
			return false;
		
		//duplicate!
		$duplicate = $source;
		$duplicate->ID = '';
		$duplicate->post_title .= __(' Copy', MAB_DOMAIN);
		$duplicate_id = wp_insert_post( $duplicate );
		$duplicate->ID = $duplicate_id;
		
		update_post_meta( $duplicate_id, $MabBase->get_meta_key( 'type' ), $MabBase->get_mab_meta( $source_id, 'type' ) );

		// update meta data
		$sourceMeta = $MabBase->get_mab_meta( $source_id );
		$dupeMeta = apply_filters('mab_duplicate_action_box_meta', $sourceMeta, $duplicate, $source);

		update_post_meta( $duplicate_id, $MabBase->get_meta_key( '' ), $dupeMeta );
		
		//TODO: add code for notices?
		update_post_meta( $source_id, $MabBase->get_meta_key('duplicate'), $duplicate_id );

		do_action('mab_duplicate_action_box', $duplicate, $source);
		
		return $duplicate_id;
	}
	
	function notifyOfDuplicate(){
		global $post, $MabBase;
		$duplicate_id = $MabBase->get_mab_meta( $post->ID, 'duplicate' );
		$filename = 'misc/duplicate-notice.php';
		$message = MAB_Utils::getView( $filename, array('duplicate-id' => $duplicate_id ) );
		echo $message;
		delete_post_meta( $post->ID, $MabBase->get_meta_key('duplicate') );
	}
	
	function processDuplicateDisplay(){
		global $pagenow, $post, $MabBase;
		if($pagenow == 'post.php' && $post->post_type == $MabBase->get_post_type()) {
			$duplicated = get_post_meta($post->ID, $MabBase->get_meta_key('duplicate'), true);
			if($duplicated) {
				add_action('admin_notices', array(&$this, 'notifyOfDuplicate'));
			}
		}
	}
	
	/**
	 * ACTION BOX UTILITY
	 * ================================================ */
	function getAvailableActionBoxTypes(){
		return $this->_data_RegisteredActionBoxes;
	}
	
	function setActionBoxType( $postId, $type ){
		global $MabBase;
		$actionBoxPost = get_post( $postId );
		if( empty( $postId) || empty( $actionBoxPost ) || !$MabBase->is_mab_post_type( $actionBoxPost->post_type ) ){
			return;
		}
		
		$availableActionBoxes = $this->getAvailableActionBoxTypes();
		if( isset( $availableActionBoxes[$type] ) ){
			update_post_meta( $postId, $MabBase->get_meta_key( 'type' ), $type );
			update_post_meta( $postId, '_wp_page_template', $type );
		}
	}
	
	function getActionBoxType( $postId ){
		global $MabBase;
		$post = get_post( $postId );
		
		//check if this is a valid post type
		//if( !$MabBase->is_mab_post_type( $post->post_type ) )
		//	return '';
		
		$availableActionBoxes = $this->getAvailableActionBoxTypes();
		
		$type = get_post_meta( $postId, $MabBase->get_meta_key( 'type' ), true );
		
		//check $type against array of available action boxes
		if( isset( $availableActionBoxes[$type] ) ){
			return $type;
			
		} else {
			return '';
		}
	}
	
	/**
	 * OPTIN PROVIDERS
	 * ================================================ */

	function initializeOptinProviders(){
		$_optin_Keys = array(
			array(
				'id' => 'aweber', 
				'name' => 'Aweber', 
				'auto_allow' => false),
			array(
				'id' => 'mailchimp', 
				'name' => 'MailChimp', 
				'auto_allow' => false),
			array(
				'id' => 'sendreach',
				'name' => 'SendReach',
				'auto_allow' => false),
			array(
				'id' => 'manual', 
				'name' => 'Other (Copy & Paste)',
				'auto_allow' => true)
		);

		$this->_optin_Providers = apply_filters('mab_set_initial_optin_providers', $_optin_Keys);
	}

	/**
	 * Register an optin provider
	 * @param  string $id  unique name
	 * @param  string $name human friendly name
	 * @param  bool   $auto_allow if FALSE, this provider will only be
	 *                            shown if it is allowed in settings
	 * @return bool FALSE if $id exists or missing $id parameter, 
	 *              otherwise returns TRUE
	 */
	function registerOptinProvider($id, $name ='', $auto_allow = false){
		if(empty($id)) return false;

		// check if $id already exists
		foreach($this->_optin_Providers as $k => $prov){
			if($prov['id'] == $id) return false;
		}

		if(empty($name))
			$name = $id;

		$this->_optin_Providers[] = array(
			'id' => $id,
			'name' => $name,
			'auto_allow' => $auto_allow
			);

		return true;
	}

	/**
	 * Returns list of allowed optin providers in an array
	 * @return array 
	 */
	function getAllowedOptinProviders(){
		/** TODO: add a registerOptinProviders() function **/
		
		$settings = ProsulumMabCommon::getSettings();
		$allowed = array();
		
		foreach( $this->_optin_Providers as $k => $provider ){
			if($provider['auto_allow']){

				$allowed[] = array('id' => $provider['id'], 'name' => $provider['name'] );

			} else {

				//add optin providers where value is not 0 or empty or null
				if( !empty( $settings['optin']['allowed'][$provider['id']] ) ){
					$allowed[] = array('id' => $provider['id'], 'name' => $provider['name'] );
				}

			}
		}
		
		//add "wysija" newsletter
		$allowed[] = array('id' => 'wysija', 'name' => 'MailPoet (Wysija)' );
		
		//add "Manual" mailing list provider
		//$allowed[] = array('id' => 'manual', 'name' => $this->_optin_Keys['manual'] );

		return $allowed;
		
	}
	
	/**
	 * Aweber
	 */
	 
	function initializeAweberApi(){
		require_once( MAB_LIB_DIR . 'aweber_api/aweber_api.php' );
	}
	
	function validateAweberAuthorizationCode($code) {
		$this->initializeAweberApi();

		try {
			list($consumer_key, $consumer_secret, $access_key, $access_secret) = AWeberAPI::getDataFromAweberID($code);
		} catch (AWeberException $e) {
			list($consumer_key, $consumer_secret, $access_key, $access_secret) = null;
		}

		if(!$access_secret) {
			return array('error' => __('Invalid Aweber authorization code.  Please make sure you entered it correctly.', 'mab' ));
		}

		$aweber = new AWeberAPI($consumer_key, $consumer_secret);

		try {
			$account = $aweber->getAccount($access_key, $access_secret);
		} catch (AWeberResponseError $e) {
			$account = null;
		}

		if(!$account) {
			return array('error' => __('Unable to connect to Aweber account.  Please try again.', 'mab' ));
		}

		return compact('consumer_key', 'consumer_secret', 'access_key', 'access_secret');
	}
	
	function getAweberLists( $forceUpdate = false ) {
		
		if( !$forceUpdate ){
			//check from cache
			$aweberLists = get_transient( $this->_optin_AweberListsTransient );
		
			if( $aweberLists !== false ){
					return $aweberLists;
			}
		}
	
		$this->initializeAweberApi();
		$settings = $this->getSettings();

		$info = $settings['optin']['aweber-account-info'];
		$aweber = new AWeberAPI($info['consumer_key'], $info['consumer_secret']);

		try {
			$account = $aweber->getAccount($info['access_key'], $info['access_secret']);
		} catch (AWeberException $e) {
			$account = null;
		}
		
		$lists = array();
		$list_web_forms = array();
		if($account) {
			foreach ($account->getWebForms() as $this_webform) {
				$link_parts = explode('/', $this_webform->url);
				$list_id = $link_parts[4];
				if (!array_key_exists($list_id, $list_web_forms)) {
					$list_web_forms[$list_id] = array(
                    'web_forms' => array(),
                    'split_tests' => array()
					);
				}
				$list_web_forms[$list_id]['web_forms'][] = $this_webform;
			}
			foreach ($account->getWebFormSplitTests() as $this_webform) {
				$link_parts = explode('/', $this_webform->url);
				$list_id = $link_parts[4];
				if (!array_key_exists($list_id, $list_web_forms)) {
					$list_web_forms[$list_id] = array(
                    'web_forms' => array(),
                    'split_tests' => array()
					);
				}
				$list_web_forms[$list_id]['split_tests'][] = $this_webform;
			}

			$lists = $account->lists;
			foreach ($lists as $this_list) {
				if (array_key_exists($this_list->id, $list_web_forms)) {
					$list_web_forms[$this_list->id]['list'] = $this_list;
				}
			}
		}

		$return = array();
		
		/* still need to figure out why we need the block of code below.
		foreach($list_web_forms as $id => $data) {
			//$item = array('id' => $id, 'name' => $data['list']->name, 'forms' => array()); //original from premise
			$item = array('id' => $data['list']->name, 'name' => $data['list']->name, 'forms' => array());
			foreach($data['web_forms'] as $web_form) {
				$item['forms'][] = array('id' => $web_form->id, 'url' => $web_form->url, 'name' => $web_form->name);
			}
			foreach($data['split_tests'] as $split_test) {
				$item['forms'][] = array('id' => $split_test->id, 'url' => $split_test->url, 'name' => sprintf(__('Split Test: %s', 'mab' ), $web_form->name));
			}
			$return[] = $item;
		}
		//*/
		
		foreach( $lists as $this_list ){
			$return[] = array( 'id' => $this_list->unique_list_id, 'name' => $this_list->name );
		}
		
		set_transient( $this->_optin_AweberListsTransient, $return, 24*60*60 ); //set for one day.
		
		return $return;
	}
	
	function getAweberActionUrl(){
		return $this->_optin_AweberFormActionUrl;
	}

	
	/**
	 * MailChimp
	 */
	function getMailChimpAccountInfo( $apikey = '' ){
		global $MabBase;
		$details = $MabBase->get_mailchimp_account_details( $apikey );
		return $details;
	}
	
	//returns all lists
	function getMailChimpLists( $forceUpdate = false ) {
		
		if( !$forceUpdate ){
			//check from cache
			$mailChimpLists = get_transient( $this->_optin_MailChimpListsTransient );
		
			if( $mailChimpLists !== false ){
				return $mailChimpLists;
			}
		}
		
		/*
		$mailChimpLists = get_transient( $this->_optin_MailChimpListsTransient );
		
		if( !($mailChimpLists === false) && ($forceUpdate === false) ){
			return $mailChimpLists;
		}
		*/
		
		require_once( MAB_LIB_DIR . 'mailchimp_api/MCAPI.class.php' );

		$settings = $this->getSettings();
		$mailchimp = new MCAPI($settings['optin']['mailchimp-api']);
		$data = $mailchimp->lists(array(), 0, 100);

		$lists = array();
		if(is_array($data) && is_array($data['data'])) {
			foreach($data['data'] as $item) {
				$lists[] = array('id' => $item['id'], 'name' => $item['name'], 'data' => $item );
			}
		}
		
		set_transient( $this->_optin_MailChimpListsTransient, $lists, 24*60*60 ); //set for one day.
		
		return $lists;
	
	}
	
	function getMailChimpListSingle( $listId ){
		
		require_once( MAB_LIB_DIR . 'mailchimp_api/MCAPI.class.php' );

		
		$settings = $this->getSettings();
		$mailchimp = new MCAPI($settings['optin']['mailchimp-api']);
		$data = $mailchimp->lists( array( 'list_id' => $listId ) );
		
		//return empty string if no data
		if( empty( $data ) )
			return '';
		
		//get only the required data in subarray
		$data = $data['data'][0];
		
		return $data;
	}

	function getMailChimpMergeVars( $id ) {
		global $MabBase;
		return $MabBase->get_mailchimp_merge_vars( $id );
	}

	function signupUserForMailChimp( $vars, $list ) {
		global $MabBase;
		return $MabBase->signup_user_mailchimp( $vars, $list );
	}

	function validateMailChimpAPIKey( $key ) {
		global $MabBase;
		return $MabBase->validate_mailchimp_key( $key );
	}


	/**
	 * SendReach API
	 * =================================
	 */
	

	function initializeSendReachApi(){
		require_once( MAB_LIB_DIR . 'integration/sendreach/api.php');
	}

	function validateSendReachApi($key, $secret){
		$this->initializeSendReachApi();

		$sendReach = new SendReachApi($key, $secret);

		if(!$sendReach->validate()){
			return array('error' => __('Invalid SendReach App Key or Secret.', 'mab'));
		}

		return true;
	}

	/**
	 * Get SendReach email list
	 * 
	 * @param  boolean $forceUpdate will check WP transient cache if
	 *                              FALSE
	 * @return array               [description]
	 */
	function getSendReachLists($forceUpdate = false){

		if( !$forceUpdate ){
			//check from cache
			$sendReachLists = get_transient( $this->_optin_SendReachListsTransient );
		
			if( $sendReachLists !== false ){
				return $sendReachLists;
			}
		}

		$lists = array();

		$settings = $this->getSettings();

		if(empty($settings['optin']['sendreach']['key']) || empty($settings['optin']['sendreach']['secret']) || empty($settings['optin']['allowed']['sendreach'])){
			return $lists;
		}

		$key = $settings['optin']['sendreach']['key'];
		$secret = $settings['optin']['sendreach']['secret'];

		$this->initializeSendReachApi();

		$sendReach = new SendReachApi($key, $secret);

		$srLists = $sendReach->lists_view();

		if($srLists->error){
			// returns empty array
			return $lists;
		}

		foreach($srLists->lists as $list){
			$lists[] = array('id' => $list->id, 'name' => $list->list_name);
		}
		
		set_transient( $this->_optin_SendReachListsTransient, $lists, 24*60*60 ); //set for one day.
		
		return $lists;
	}
	
	/**
	 * Constant Contact
	 */
	function setupConstantContactUtility($apikey, $username, $password, $action = 'ACTION_BY_CONTACT') {
		CCUtility::$sapikey = $apikey;
		CCUtility::$susername = $username;
		CCUtility::$spassword = $password;
		CCUtility::$saction = $action;
	}
	
	function getConstantContactLists( $forceUpdate = false ) {
		require_once( MAB_LIB_DIR . 'constant_contact_api/constant_contact_api.php' );

		$settings = $this->getSettings();
		$optin = $settings['optin'];
		$this->setupConstantContactUtility($this->_optin_ConstantContactKey, $optin['constant-contact-username'], $optin['constant-contact-password']);

		$lists = new ListsCollection();
		list($items) = $lists->getLists();

		$return = array();
		foreach($items as $item) {
			$return[] = array('id' => preg_replace('/[^0-9]/', '', (string)$item->getId()), 'name' => (string)$item->getName());
		}
		return array_slice($return, 3);
	}
	
	function signupUserForConstantContact($firstname, $lastname, $email, $list) {
		require_once( MAB_LIB_DIR . 'constant_contact_api/constant_contact_api.php' );

		$settings = $this->getSettings();
		$optin = $settings['optin'];
		$this->setupConstantContactUtility($this->_optin_ConstantContactKey, $optin['constant-contact-username'], $optin['constant-contact-password']);

		
		//check if user exists
		$collection = new ContactsCollection();
		list($search) = $collection->searchByEmail($email);
		if(!empty($search)) {
			foreach($search as $possible) {
				if($email == $possible->getEmailAddress()) {
					$contact = $collection->getContact($possible->getLink());
					break;
				}
			}
		}

		$listKey = 'http://api.constantcontact.com/ws/customers/'.$optin['constant-contact-username'].'/lists/'.$list;
		if($contact) {
			$existingLists = $contact->getLists();

			if(in_array($listKey, $existingLists)) {
				return array('error' => __('You have already subscribed to this mailing list', 'mab' ));
			} else {
				$contact->setLists($listKey);
			}
			$contact->setFirstName($firstname);
			$contact->setLastName($lastname);
			$result = $collection->updateContact($contact->getId(), $contact);
		} else {
			$contact = new Contact();
			$contact->setFirstName($firstname);
			$contact->setLastName($lastname);
			$contact->setEmailAddress($email);
			$contact->setLists($listKey);

			$result = $collection->createContact($contact);
		}

		$first = substr((string)$result,0,1);

		if($first == 2) {
			return true;
		} else {
			return array('error' => __('Could not subscribe you to this mailing list', 'mab' ));
		}
	}
	
	function validateConstantContactCredentials($username, $password) {
		require_once( MAB_LIB_DIR . 'constant_contact_api/constant_contact_api.php' );

		$this->setupConstantContactUtility($this->_optin_ConstantContactKey, $username, $password);

		$utility = new CCUtility();
		$result = $utility->ping();

		if(!empty($result['error'])) {
			return array('error' => __('Invalid Constant Contact credentials.', 'mab' ));
		}

		return true;
	}
	
	
	/**
	 * FUNCTIONAL CALLBACKS
	 * ================================ */
	
	//Opt In AJAX
	function ajaxOptinGetLists(){
		$data = stripslashes_deep( $_POST );
		
		$lists = array();
		switch( $data['provider'] ){
			case 'aweber':
				$lists = $this->getAweberLists(true);//TRUE - don't get from cache
				break;
			case 'mailchimp':
				$lists = $this->getMailChimpLists( true ); //TRUE - don't get from cache
				break;
			case 'sendreach':
				$lists = $this->getSendReachLists(true);
				break;
		}
		
		echo json_encode( $lists );
		exit();
	}
	
	//Process Opt In Code
	function ajaxProcessOptinCode(){
		$regex_form = '/(<form\b[^>]*>)(.*?)(<\/form>)/ims';
		//allowed fields <input>, <select>, <button>
		$regex_input = '/(.*?)(<input\b[^>]*>|<select(?!<\/select>).*?<\/select>|<textarea(?!<\/textarea>).*?<\/textarea>|<button(?!<\/button>).*?<\/button>)/ims';
		
		$formComponents = array();
		$data = stripslashes_deep( $_POST );
		
		$optinCode = $data['optinFormCode'];
		$submitvalue = $data['submitValue'];
		$submitImage = $data['submitImage'];
		
		//process the code
		preg_match($regex_form, $optinCode, $formComponents);
		
		//opening <form>
		$formArr = $this->optinProcessTagAttributes( $formComponents[1] );
		
		$newForm = $formArr['tag']."\n";
		//$newForm = $formComponents[1] . "\n";
		
		//body of the form. process inputs and labels
		preg_match_all ($regex_input, $formComponents[2], $inputs);
		//error_log(print_r($inputs[2],true) );
		//process for each <input>
		foreach( $inputs[2] as $key => $input ){
			//clean out attributes of <input> tag
			$inputTag = $this->optinProcessTagAttributes( $input );
			
			$theTag = $inputTag['tag'];
			$tagType = $inputTag['type'];
			$inputType = $inputTag['input-type'];
			
			//get the ID of <input> tag
			$inputTagId = $this->optinGetTagId( $theTag );
			$label = $this->optinPrepareLabel( $inputs[1][$key], $inputTagId );
			
			if( $this->optinIsNotFieldTag( $theTag ) && ($inputType == 'submit' || $inputType == 'image' || $inputType == 'button' ) ){
				//must be submit
				$newForm .= '<div class="mab-field">'."\n";
				$newForm .= trim("{$theTag}")."\n";
				$newForm .= '</div>'."\n";
			} elseif( $this->optinIsNotFieldTag( $theTag ) ){
				//must some other tag i.e. hidden fields
				$newForm .= trim("{$label} {$theTag}")."\n";
			} else {
				//field wrapper
				$newForm .= '<div class="mab-field">'."\n";
				//label and input
				$newForm .= trim("{$label} {$theTag}")."\n";
				$newForm .= '</div>'."\n";
			}
		}
		
		//$newForm .= $formComponents[2] . "\n";
		
		//add clearing div
		$newForm .= '<div class="clear"></div>' . "\n";
		
		//closing </form>
		$newForm .= $formComponents[3] . "\n";
		
		echo json_encode($newForm);
		exit();
	}
	
	/**
	 * @return array
	 *         arrayf type  - the type of tag. input|button|select
	 *         arrayf tag   - the whole tag
	 */
	function optinProcessTagAttributes( $tag ){
		
		$submitClass = 'mab-optin-submit';
		$unwanted_attributes = array('style','class','onsubmit','target','tabindex','onclick');
		$unpairedTags = array('input');
		$input_type = '';
		$input_value = '';
		$temp_attribute_value = '';
		$is_self_closing = true; //true for <input> tags
		
		//grab the field type
		$regex_tagtype = '/<([a-zA-Z]*?)[\s>]/i';
		preg_match($regex_tagtype, $tag, $htmltagtype); 
		$tag_type = $htmltagtype[1];

		if( $tag_type == 'button' ){
			//convert $tag_type to input
			$tag_type = 'input';
			
			//discard button and use <input type="submit" /> instead
			$tag_opening = '<input type="submit" value="Submit" />';
			$tag_body = '';
		} else {
		
			//grab the opening tag with attributes and the body of the tag plus closing tag
			$regex_tag = '/(<'.$tag_type.'\b[^>]*>)(.*)/ims';
			preg_match( $regex_tag, $tag, $the_tag );
			$tag_opening = $the_tag[1];
			$tag_body = trim($the_tag[2]);
		}
		
		//grab the attributes
		$regex_attribute = '/([^\s]*?)=(["\'])?(?(2)(.*?)["\']|(.*?)[\s|>])/ims';
		preg_match_all($regex_attribute, $tag_opening, $attributes);
		
		//begin the tag
		$newtag = '<'.$tag_type;
		
		//take out unwanted attributes
		foreach( $attributes[1] as $key => $attribute ){
			if( !in_array( $attribute, $unwanted_attributes ) ){
			
				$attribute_value = ( $attributes[2][$key] == '"' || $attributes[2][$key] == "'" ) ? $attributes[3][$key] : $attributes[4][$key];
				
				//convert input type="image" and type="button" to type="submit"
				if( $attribute=='type' && ( $attribute_value == 'image' || $attribute_value == 'button' ) ){
					
					//write the attribute
					$newtag .= ' type="submit"';
					
					//change attribute value to "submit" since we don't want type="image" or buttons
					//we will also process this later
					$attribute_value = 'submit';
				
				} elseif( $attribute == 'value' ){
					//store tag value for now. we will put it in later after we can determine
					//what kind of tag we are dealing with.
					$temp_attribute_value = $attribute_value;
					
				} else {
					$newtag .= ' ' . $attribute . '="' . $attribute_value . '"';
				}
				
				//for type="submit" only. Add $submitClass
				if( $attribute == 'type' && $attribute_value == 'submit' ){
				
					//$newtag .= ' class="' . $submitClass . '"';
					
					//mark this tag as type="submit"
					$input_type = 'submit';
				}
				
				/*
				//check for "value" attribute
				if( $attribute == 'value' ){
					//store value
					$input_value = $attribute_value;
				}
				*/

			}
		}
		
		//do stuff if tag is submit/image/button type
		if( $input_type == 'submit' ){
			
			//add a class to the submit button
			$newtag .= ' class="' . $submitClass . '"';
			
			//set submit button text
			//get specified submit value if it is specified
			$data = stripslashes_deep( $_POST );
			$submit_value = $data['submitValue'];
			
			if( !empty( $submit_value ) ){
				$temp_attribute_value = $submit_value;
			} else {
				//specify submit value if attribute is not present
				if( $temp_attribute_value == '' ){
					$temp_attribute_value = 'Submit';
				}
			}
			
		}
		
		//add the attribute value if it is saved
		if( $temp_attribute_value !== '' ){
			$newtag .= ' value="' . $temp_attribute_value . '"';
		}
		
		if( in_array( strtolower( $tag_type ), $unpairedTags ) ){
			$is_self_closing = true;
			$close = ' />';
		} else {
			$is_self_closing = false;
			//close the opening tag
			$close = '>';
			//add the rest of the tag
			$close .= $tag_body;
		}
		
		$newtag .= $close;
		
		//take out src and alt tags
		$regex_src_alt = '/(alt=".*?")|src=".*?"/i';
		$newtag = preg_replace( $regex_src_alt, '', $newtag );
		
		// if submitImage is available, then turn submit buttons into
		// <input type="image">
		if($input_type == 'submit' && !empty($data['submitImage'])){
			// create new tag
			$newtag = sprintf('<input type="image" class="%1$s mab-optin-submit-image" src="%2$s" alt="Submit">', $submitClass, $data['submitImage']);
		}

		$out = array(
			'tag' => $newtag,
			'type' => $tag_type,
			'input-type' => $input_type
		);
		//if( $tag_type == 'form' ) error_log( print_r( $out, true ) );
		return $out;
	}
	
	function optinSubmitButtonValue( $tag, $submitValue ){
		$regex = '/^.*?value="[\s]*([a-zA-Z0-9][-a-zA-Z0-9]+)[\s]*"/i';
		
		
	}
	
	function optinPrepareLabel( $block, $for ){
		$regex_htmltag = '/(<[^>]+>)/ims';
		$regex_label = '/<label.*?>(.*?)<\/label>/is';
		
		//try to get the correct label
		$matchCount = preg_match( $regex_label, $block, $label );
		
		if( empty( $matchCount ) ){
			//create label by removing all html stuff.
			$label = trim( preg_replace( $regex_htmltag, '', $block ) );
		} else {
			//process label
			$label = trim( $label[1] );
		}
			
		if( $label != '' )
			$label = '<label for="' . $for . '">' . $label .'</label>';
		
		return $label;
	}
	
	function optinGetTagId( $tag ){
		$regex = '/^.*?id="[\s]*([a-zA-Z0-9][-a-zA-Z0-9]+)[\s]*"/i';
		
		$matchCount = preg_match( $regex, $tag, $id );
		
		$return = empty( $matchCount ) ? '' : $id[1];
		
		return $return;
	}
	
	function optinIsNotFieldTag( $tag ){
		$regex = '/[\s]+type="(hidden|submit)"[\s]+/i';
		//match "hidden" or "submit"
		$matchCount = preg_match( $regex, $tag );
		if( empty( $matchCount ) ){
			return false;
		} else {
			return true;
		}
		
	}
	
	/**
	 * CSS, JAVASCRIPT, THICKBOX
	 * ================================ */	
	function enqueueStylesForAdminPages(){
		global $MabBase, $pagenow, $post;
		
		$is_mab_post_type = true;
		if( !is_object( $post ) ){
			$is_mab_post_type = false;
		} else {
			$is_mab_post_type = $MabBase->is_mab_post_type( $post->post_type );
		}
		
		//don't load if post content type doesn't require it.
		if( $MabBase->is_allowed_content_type() ){

			wp_enqueue_style( 'mab-admin-style' );
			
		} elseif( $is_mab_post_type || $pagenow == 'admin.php' ){
			/* create custom buttons stylesheet if its not there */
			if( !file_exists( mab_get_custom_buttons_stylesheet_path() ) ){
				global $MabButton;
				$MabButton->writeConfiguredButtonsStylesheet( $MabButton->getConfiguredButtons(), '' );
			}
			//load buttons stylesheet
			wp_enqueue_style( 'mab-custom-buttons', mab_get_custom_buttons_stylesheet_url() );
			//load admin stuff stylesheet
			wp_enqueue_style( 'mab-admin-style' );
			//used for color pickers
			wp_enqueue_style( 'farbtastic' );
		}
	}
	
	function enqueueScriptsForAdminPages(){
		global $MabBase, $pagenow, $post;

		$is_mab_post_type = true;
		if( !is_object( $post ) ){
			$is_mab_post_type = false;
		} else {
			$is_mab_post_type = $MabBase->is_mab_post_type( $post->post_type );
		}

		//don't load if post content type doesn't require it.
		if( $MabBase->is_allowed_content_type() ){
			
		} elseif( $is_mab_post_type || $pagenow == 'admin.php' ){
			add_thickbox();
			wp_enqueue_script('media-upload');
			
			wp_enqueue_script( 'farbtastic' );
			wp_enqueue_script( 'mab-admin-script' );
			wp_enqueue_script( 'mab-design-script' );
		}
	}
	
	function createStyleSheet( $key, $section = 'all' ){
		global $MabBase;
		$MabBase->create_stylesheet( $key, $section );
	}
	
	function createActionBoxStylesheet( $postId, $section = 'all' ){
		global $MabBase;
		$MabBase->create_actionbox_stylesheet( $postId, $section );
	}
	
	function possiblyStartOutputBuffering(){
		global $pagenow, $MabBase;
		if($pagenow == 'post-new.php' && isset( $_GET['post_type'] )  && $_GET['post_type'] == $MabBase->get_post_type() ) {
			ob_start();
		}
	}
	
	function possiblyEndOutputBuffering(){
		global $pagenow, $MabBase;
		
		if($pagenow == 'post-new.php' && isset( $_GET['post_type'] ) && $_GET['post_type'] == $MabBase->get_post_type()) {
			$result = ob_get_clean();
			$filename = 'interceptions/post-new.php';	
			$data = $result;
			$view = ProsulumMabCommon::getView( $filename, $data );
			echo $view;
		}
	}
	
}
