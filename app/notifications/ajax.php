<?php
/*
+--------------------------------------------------------------------------
|   WeCenter [#RELEASE_VERSION#]
|   ========================================
|   by WeCenter Software
|   © 2011 - 2014 WeCenter. All Rights Reserved
|   http://www.wecenter.com
|   ========================================
|   Support: WeCenter@qq.com
|
+---------------------------------------------------------------------------
*/

define('IN_AJAX', TRUE);

if (!defined('IN_ANWSION'))
{
	die;
}

class ajax extends AWS_CONTROLLER
{
	var $per_page;

	public function get_access_rule()
	{
		$rule_action['rule_type'] = "white"; //黑名单,黑名单中的检查  'white'白名单,白名单以外的检查
		$rule_action['actions'] = array();
		return $rule_action;
	}

	function setup()
	{
		HTTP::no_cache_header();

		$this->per_page = get_setting('notifications_per_page');
	}

	public function commentcount_action()
	{
		H::ajax_json_output($this->model('account')->get_comment_notification_count($this->user_id));
	}

	public function listinbox_action()
	{
		if ($this->user_id)
		{
			if ($inbox_dialog = $this->model('message')->get_inbox_message($_GET['page'], get_setting('contents_per_page'), $this->user_id))
			{
				$inbox_total_rows = $this->model('message')->found_rows();

				foreach ($inbox_dialog as $key => $val)
				{
					$dialog_ids[] = $val['id'];

					if ($this->user_id == $val['recipient_uid'])
					{
						$inbox_dialog_uids[] = $val['sender_uid'];
					}
					else
					{
						$inbox_dialog_uids[] = $val['recipient_uid'];
					}
				}
			}

			if ($inbox_dialog_uids)
			{
				if ($users_info_query = $this->model('account')->get_user_info_by_uids($inbox_dialog_uids))
				{
					foreach ($users_info_query as $user)
					{
						$users_info[$user['uid']] = $user;
					}
				}
			}
		//
			if ($dialog_ids)
			{
				$last_message = $this->model('message')->get_last_messages($dialog_ids);
			}

			if ($inbox_dialog)
			{
				foreach ($inbox_dialog as $key => $value)
				{
					if ($value['recipient_uid'] == $this->user_id AND $value['recipient_count']) // 当前处于接收用户
					{
						$data[$key]['user_name'] = $users_info[$value['sender_uid']]['user_name'];
						$data[$key]['url_token'] = $users_info[$value['sender_uid']]['url_token'];

						$data[$key]['unread'] = $value['recipient_unread'];
						$data[$key]['count'] = $value['recipient_count'];

						$data[$key]['uid'] = $value['sender_uid'];
					}
					else if ($value['sender_uid'] == $this->user_id AND $value['sender_count']) // 当前处于发送用户
					{
						$data[$key]['user_name'] = $users_info[$value['recipient_uid']]['user_name'];
						$data[$key]['url_token'] = $users_info[$value['recipient_uid']]['url_token'];

						$data[$key]['unread'] = $value['sender_unread'];
						$data[$key]['count'] = $value['sender_count'];
						$data[$key]['uid'] = $value['recipient_uid'];
					}

					$data[$key]['last_message'] = $last_message[$value['id']];
					$data[$key]['update_time'] = $value['update_time'];
					$data[$key]['id'] = $value['id'];
				}
			}
		}

		TPL::assign('inbox_list', $data);
		TPL::output("notifications/ajax/listinbox");
	}

	public function list_action()
	{
		if ($_GET['limit'])
		{
			$per_page = intval($_GET['limit']);
		}
		else
		{
			$per_page = $this->per_page;
		}

		if ($_GET['type'] == 'comment') {
			$list = $this->model('notify')->list_custom_notification($this->user_id, $_GET['flag'], intval($_GET['page']) * $per_page . ', ' . $per_page, true);
		}
		if ($_GET['type'] == 'nocomment'){
			$list = $this->model('notify')->list_notification($this->user_id, $_GET['flag'], intval($_GET['page']) * $per_page . ', ' . $per_page, false);
		}


		if (!$list AND $this->user_info['notification_unread'] != 0)
		{
			$this->model('account')->update_notification_unread($this->user_id);
		}

		TPL::assign('flag', $_GET['flag']);
		TPL::assign('list', $list);

		if ($_GET['template'] == 'header_list')
		{
			TPL::output("notifications/ajax/header_list");
		}
		else if (is_mobile())
		{
			TPL::output('m/ajax/notifications_list');
		}
		else
		{
			TPL::output("notifications/ajax/list");
		}
	}

	public function read_notification_action()
	{
		if (isset($_GET['notification_id']))
		{
			$this->model('notify')->read_notification($_GET['notification_id'], $this->user_id);
		}
		else
		{
			$this->model('notify')->mark_read_all($this->user_id);
		}

		H::ajax_json_output(AWS_APP::RSM(null, 1, null));
	}
}
