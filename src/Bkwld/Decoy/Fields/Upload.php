<?php namespace Bkwld\Decoy\Fields;

// Dependencies
use Bkwld\Library;
use Config;
use Former\Form\Fields\File;
use HtmlObject\Input as HtmlInput;
use Illuminate\Container\Container;
use Str;

/**
 * Creates a file upload field with addtional UI for reviewing the last upload
 * and deleting it.
 */
class Upload extends File {
	use Traits\CaptureBlockHelp;

	/**
	 * Create a regular file type field
	 *
	 * @param Container $app        The Illuminate Container
	 * @param string    $type       text
	 * @param string    $name       Field name
	 * @param string    $label      Its label
	 * @param string    $value      Its value
	 * @param array     $attributes Attributes
	 */
	public function __construct(Container $app, $type, $name, $label, $value, $attributes) {
		parent::__construct($app, 'file', $name, $label, $value, $attributes);
		$this->addGroupClass('upload');
	}

	/**
	 * Prints out the field, wrapped in its group.  This is the opportunity
	 * to tack additional stuff into the blockhelp before it is rendered
	 * 
	 * @return string
	 */
	public function wrapAndRender() {

		// Wrap manually set help text in a wrapper class
		$help = $this->blockhelp;
		if ($help) $help = '<span class="regular-help">'.$help.'</span>';

		// Append the review UI to the blockhelp
		if ($this->value) $help .= $this->renderReview();

		// Apply all of the blockhelp arguments to the group
		if ($help) $this->group->blockhelp($help, $this->blockhelp_attributes);

		// Continue doing normal wrapping
		return parent::wrapAndRender();
	}

	/**
	 * Prints out the current tag. The hidden field with the current upload is
	 * prepended before the input so it can be overriden
	 *
	 * @return string An input tag
	 */
	public function render() {

		// A file has already been uploaded
		if ($this->value) {

			// If it's required, show the icon but don't enforce it.  There is already
			// a file uploaded after all
			if ($this->isRequired()) $this->setAttribute('required', null);

			// Add hidden field and return
			return $this->renderHidden().parent::render();
		
		// The field is empty
		} else return parent::render();
	}

	/**
	 * Render the hidden field that contains the currently uploaded file
	 *
	 * @return string A hidden field
	 */
	protected function renderHidden() {
		return HtmlInput::hidden($this->name, $this->value);
	}

	/**
	 * Render the display of the currently uploaded item
	 *
	 * @return string HTML
	 */
	protected function renderReview() {
		if (!$this->isRequired() && $this->isInUploads()) return $this->renderDestuctableReview();
		else return $this->renderIndestructibleReview();
	}

	/**
	 * Check if the file is in the uploads directory. The use case for this arose 
	 * with the Fragments system where the default images would usually be in the 
	 * img directory
	 *
	 * @return boolean
	 */
	protected function isInUploads() {
		$upload_dir = Library\Utils\File::publicPath(Config::get('decoy::upload_dir'));
		return Str::is($upload_dir.'*', $this->value);
	}

	/**
	 * Show the preview UI with a delete checkbox
	 *
	 * @return string HTML
	 */
	protected function renderDestuctableReview() {
		return '<label for="'.$this->name.'-delete" class="checkbox upload-delete">
			<input id="'.$this->name.'-delete" type="checkbox" name="'.$this->name.'" value="">
			Delete '.$this->renderDownloadLink().'
			</label>';
	}

	/**
	 * Show the preview UI withOUT a delete checkbox
	 *
	 * @return string HTML
	 */
	protected function renderIndestructibleReview() {
		return '<label class="download">
			Currently '.$this->renderDownloadLink().'
			</label>';
	}

	/**
	 * Make the downloadable link to the file
	 *
	 * @return string HTML
	 */
	protected function renderDownloadLink() {
		return '<a href="'.$this->value.'">
			<code><i class="icon-file"></i>'.basename($this->value).'</code>
			</a>';
	}

}