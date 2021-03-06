<?php
/**
 * Compose view class
 * @package YetiForce.View
 * @copyright YetiForce Sp. z o.o.
 * @license YetiForce Public License 2.0 (licenses/License.html or yetiforce.com)
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

/**
 * Compose view class
 */
class OSSMail_Compose_View extends OSSMail_index_View
{

	/**
	 * Pre process
	 * @param \App\Request $request
	 * @param bool $display
	 */
	public function preProcess(\App\Request $request, $display = true)
	{
		$this->initAutologin();
	}

	/**
	 * Process
	 * @param \App\Request $request
	 */
	public function process(\App\Request $request)
	{
		$currentUser = Users_Record_Model::getCurrentUserModel();
		if (strpos($this->mainUrl, '?') !== false) {
			$this->mainUrl .= '&';
		} else {
			$this->mainUrl .= '?';
		}
		$this->mainUrl .= '_task=mail&_action=compose&_extwin=1';
		$params = OSSMail_Module_Model::getComposeParam($request);
		$key = md5(count($params) . microtime());

		$dbCommand = \App\Db::getInstance()->createCommand();
		$dbCommand->delete('u_#__mail_compose_data', ['userid' => $currentUser->getId()])->execute();
		$dbCommand->insert('u_#__mail_compose_data', ['key' => $key, 'userid' => $currentUser->getId(), 'data' => \App\Json::encode($params)])->execute();
		$this->mainUrl .= '&_composeKey=' . $key;
		header('Location: ' . $this->mainUrl);
	}

	/**
	 * Post process
	 * @param \App\Request $request
	 */
	public function postProcess(\App\Request $request)
	{

	}
}
