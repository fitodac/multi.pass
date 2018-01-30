<?php

if (!class_exists('WDS_Check_Abstract')) require_once(dirname(__FILE__) . '/class_wds_check_abstract.php');

class WDS_Check_Title_Keywords extends WDS_Check_Post_Abstract {

	private $_state;

	public function get_status_msg () {
		if (-1 === $this->_state) return __('We couldn\'t find a title to check for keywords', 'wds');
		return $this->_state === false
			? __('Title has no keywords', 'wds')
			: __('Title contains keywords', 'wds')
		;
	}

	public function apply () {
		$post = $this->get_subject();
		$subject = false;
		$resolver = false;

		if (!is_object($post) || empty($post->ID)) {
			$subject = $this->get_markup();
		} else {
			$resolver = WDS_Endpoint_Resolver::resolve();
			$resolver->simulate_post($post->ID);

			$subject = WDS_OnPage::get()->get_title();
		}

		if ($resolver) $resolver->stop_simulation();
		return !!$this->_state = $this->has_focus($subject);
	}

	public function apply_html () {
		$titles = WDS_Html::find_content('title', $this->get_markup());
		if (empty($titles)) {
			$this->_state = -1;
			return false;
		}

		$title = reset($titles);
		if (empty($title)) {
			$this->_state = -1;
			return false;
		}

		return !!$this->_state = $this->has_focus($title);
	}

	public function get_recommendation()
	{
		if ($this->_state) {
			$message = __("You've managed to get your focus keywords in your SEO title which has the best chance of matching what users are searching for first up - nice work.", 'wds');
		} else {
			$message = __("The focus keyword for this article doesn't appear in the SEO title which means it has less of a chance of matching what your visitors will search for.", 'wds');
		}

		return $message;
	}

	public function get_more_info()
	{
		return __("It's considered good practice to try to include your focus keyword(s) in the SEO title of a page because this is what people looking for the article are likely searching for. The higher chance of a keyword match, the greater the chance that your article will be found higher up in search results. Whilst it's recommended to try and get these words in, don't sacrifice readability and the quality of the SEO title just to rank higher - people may not want to click on it if it doesn't read well.", 'wds');
	}
}