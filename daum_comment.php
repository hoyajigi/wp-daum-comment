<?php
/*
Plugin Name: Daum Profile
Plugin URI: http://github.com/daumdna
Description: Social comments with Daum Profile API.
Version: 0.1
Author: Hyunseok Cho
Author URI: http://dna.daum.net
License: GPL2
*/

/*
This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
*/

// if redirect page
if ( !function_exists( 'add_action' ) ) {
?>
<script type="text/javascript">
	try{
		name=(new RegExp('[#&]'+encodeURIComponent("access_token")+'=([^&]*)')).exec(location.href)[1];
		if(name){
			var d = new Date();
			d.setTime(d.getTime()+24*60*60*1000);
			var expires = "expires="+d.toGMTString();
			document.cookie = "DAUM_OAUTH2_ACCESS_TOKEN" + "=" + name + "; " + expires+"; path=/";
			window.opener.location.reload(false);
			self.opener = self;
			window.close();
		}
	}
	catch(e){}
</script>
<?php
	exit;
}


if (!class_exists('DaumProfileComments')){

    class DaumProfileComments {
        public $url = '';
        public $clientid = '987612341234';

        function __construct() {
        	$this->url = plugins_url('assets/', __FILE__);
			
			add_filter('comment_form_top',array($this, 'add_socialcomments_tmplate'));
			add_action( 'wp_enqueue_scripts', array($this,'enqueue_style' ));
			add_filter('wp_get_current_commenter',array($this,'invoke_commenter'));
        }

        function getAccessTokenFromCookie() {
			if(isset($_COOKIE["DAUM_OAUTH2_ACCESS_TOKEN"]))
				return $_COOKIE["DAUM_OAUTH2_ACCESS_TOKEN"];
			return false;
		}
		
		function invoke_commenter($arg) {
			if($this->getAccessTokenFromCookie()){
				$response=wp_remote_get( 'https://apis.daum.net/user/v1/show.json?access_token='.$this->getAccessTokenFromCookie());
				$body=$response["body"];
				$json_object=json_decode($body);
				$arg["comment_author"]=$json_object->result->nickname;
				$arg["comment_author_email"]="@hanmail.net";
			}
			return $arg;
		}

		function add_socialcomments_tmplate() {
			if(!$this->getAccessTokenFromCookie()){ ?>
				<a href="#" onclick="javascript:window.open('https://apis.daum.net/oauth2/authorize?client_id=<?php echo $this->clientid; ?>&redirect_uri=<?php echo urlencode(plugins_url('/daum_comment.php', __FILE__)); ?>&response_type=token', '', 'width=800, height=700, toolbar=no, menubar=no, scrollbars=no, resizable=yes' ); " class="daumButton"></a>
	<?php   }
		}

		function enqueue_style() {
			wp_register_style( 'social_comments_rtl', $this->url.'css/style.css');
			wp_enqueue_style( 'social_comments_rtl' );
		}
    } // end class
}//end if
$GLOBALS['DaumProfileComments'] = new DaumProfileComments();
?>