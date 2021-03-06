<?php
/**
 * Author: Hoang Ngo
 */

namespace WP_Defender\Module\Hardener;

use Hammer\Helper\HTTP_Helper;
use Hammer\WP\Component;
use WP_Defender\Module\Hardener\Model\Settings;

/**
 * Class Rule
 * @package WP_Defender\Module\Hardener
 */
abstract class Rule extends Component {

	/**
	 *
	 * @var string
	 */
	static $slug;

	/**
	 * Return this rule content, we will try to use renderPartial
	 *
	 * @return mixed
	 */
	abstract function getDescription();

	/**
	 * @return mixed
	 */
	abstract function check();

	/**
	 * implement the revert function
	 *
	 * @return mixed
	 */
	abstract function revert();

	/**
	 * implement the process function
	 * @return mixed
	 */
	abstract function process();

	/**
	 * @return mixed
	 */
	abstract function getTitle();

	/**
	 * @return mixed
	 */
	public function ignore() {
		$setting = Settings::instance();
		$setting->addToIgnore( static::$slug );
	}

	/**
	 *
	 */
	public function restore() {
		$setting = Settings::instance();
		$setting->addToIssues( static::$slug );
	}

	/**
	 * Return Service class
	 * @return mixed
	 */
	abstract function getService();

	/**
	 * generate a nonce field
	 */
	public function createNonceField() {
		wp_nonce_field( self::$slug, '_wdnonce' );
	}

	/**
	 * @return mixed
	 */
	abstract function addHooks();

	/**
	 * @return false|int
	 */
	public function verifyNonce() {
		if ( $_SERVER['REQUEST_METHOD'] != 'POST' ) {
			return false;
		}

		$nonce = HTTP_Helper::retrieve_post( '_wdnonce' );

		return wp_verify_nonce( $nonce, self::$slug );
	}

	/**
	 * Show ignore form
	 */
	public function showIgnoreForm() {
		?>
        <form method="post" class="hardener-frm ignore-frm rule-process">
			<?php $this->createNonceField(); ?>
            <input type="hidden" name="action" value="ignoreHardener"/>
            <input type="hidden" name="slug" value="<?php echo static::$slug ?>"/>
            <button type="submit" name="ignore" value="ignore" class="sui-button sui-button-ghost">
                <i class="sui-icon-eye-hide" aria-hidden="true"></i> <?php _e( "Ignore", wp_defender()->domain ) ?>
            </button>
        </form>
		<?php
	}

	/**
	 * @return bool
	 */
	public function isIgnored() {
		$ignored = Settings::instance()->ignore;

		return in_array( static::$slug, $ignored );
	}

	public function showRestoreForm() {
		?>
        <div class="sui-accordion sui-accordion-flushed">
            <div class="sui-accordion-item accordion-ignore">
                <div class="sui-accordion-item-header">
                    <div class="sui-accordion-item-title">
                        <i aria-hidden="true" class="sui-icon-eye-hide"></i>
	                    <?php echo $this->getTitle(); ?>
                        <div class="sui-actions-right">
                            <form method="post" class="float-r hardener-frm rule-process">
		                        <?php $this->createNonceField(); ?>
                                <input type="hidden" name="action" value="restoreHardener"/>
                                <input type="hidden" name="slug" value="<?php echo static::$slug ?>"/>
                                <button type="submit" class="sui-button sui-button-ghost">
                                    <i class="sui-icon-update" aria-hidden="true"></i>
			                        <?php _e( "Restore", wp_defender()->domain ) ?>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>

		<?php
	}

	/**
	 * @return string
	 */
	public function getCssClass() {
		if ( $this->isIgnored() ) {
			return '';
		}

		if ( $this->check() ) {
			return 'sui-success';
		}

		return 'sui-warning';
	}

	/**
	 * @return array
	 */
	public function behaviors() {
		return array(
			'utils' => '\WP_Defender\Behavior\Utils'
		);
	}
}