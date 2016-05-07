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


if (!defined('IN_ANWSION'))
{
	die;
}

class main extends AWS_CONTROLLER
{
	public function get_access_rule()
	{
		$rule_action['rule_type'] = "white"; //'black'黑名单,黑名单中的检查  'white'白名单,白名单以外的检查

		if ($this->user_info['permission']['visit_explore'] AND $this->user_info['permission']['visit_site'])
		{
			$rule_action['actions'][] = 'index';
		}

		return $rule_action;
	}

	public function setup()
	{
		if (is_mobile() AND !$_GET['ignore_ua_check'])
		{
			switch ($_GET['app'])
			{
				default:
					HTTP::redirect('/m/');
				break;
			}
		}
	}

	public function index_action()
	{
		if (is_mobile())
		{
			HTTP::redirect('/m/explore/' . $_GET['id']);
		}

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

			TPL::assign('inbox_list', $data);

			$this->crumb(AWS_APP::lang()->_t('发现'), '/explore');

			if (! $this->user_info['email'])
			{
				HTTP::redirect('/account/complete_profile/');
			}
		}

		if ($_GET['category'])
		{
			if (is_digits($_GET['category']))
			{
				$category_info = $this->model('system')->get_category_info($_GET['category']);
			}
			else
			{
				$category_info = $this->model('system')->get_category_info_by_url_token($_GET['category']);
			}
		}

		if ($category_info)
		{
			TPL::assign('category_info', $category_info);

			$this->crumb($category_info['title'], '/category-' . $category_info['id']);

			$meta_description = $category_info['title'];

			if ($category_info['description'])
			{
				$meta_description .= ' - ' . $category_info['description'];
			}

			TPL::set_meta('description', $meta_description);
		}

		// 导航
		if (TPL::is_output('block/content_nav_menu.tpl.htm', 'explore/index'))
		{
			TPL::assign('content_nav_menu', $this->model('menu')->get_nav_menu_list('explore'));
		}

		// 边栏可能感兴趣的人
		if (TPL::is_output('block/sidebar_recommend_users_topics.tpl.htm', 'explore/index'))
		{
			TPL::assign('sidebar_recommend_users_topics', $this->model('module')->recommend_users_topics($this->user_id));
		}

		// 边栏热门用户
		if (TPL::is_output('block/sidebar_hot_users.tpl.htm', 'explore/index'))
		{
			TPL::assign('sidebar_hot_users', $this->model('module')->sidebar_hot_users($this->user_id, 5));
		}

		// 边栏热门话题
		if (TPL::is_output('block/sidebar_hot_topics.tpl.htm', 'explore/index'))
		{
			TPL::assign('sidebar_hot_topics', $this->model('module')->sidebar_hot_topics($category_info['id']));
		}

		// 边栏专题
		if (TPL::is_output('block/sidebar_feature.tpl.htm', 'explore/index'))
		{
			TPL::assign('feature_list', $this->model('module')->feature_list());
		}

		if (! $_GET['sort_type'] AND !$_GET['is_recommend'])
		{
			$_GET['sort_type'] = 'new';
		}

		if ($_GET['sort_type'] == 'hot')
		{
			$posts_list = $this->model('posts')->get_hot_posts(null, $category_info['id'], null, $_GET['day'], $_GET['page'], get_setting('contents_per_page'));
		}
		else
		{
			$posts_list = $this->model('posts')->get_posts_list(null, $_GET['page'], get_setting('contents_per_page'), $_GET['sort_type'], null, $category_info['id'], $_GET['answer_count'], $_GET['day'], $_GET['is_recommend']);
		}

		if ($posts_list)
		{
			foreach ($posts_list AS $key => $val)
			{
				$posts_list[$key]['custom_user_info'] = $this->model('account')->get_user_info_by_uid($val['uid']);
				$posts_list[$key]['custom_user_info']['is_followed'] = $this->model('follow')->user_follow_check($this->user_id, $val['uid']);

				if ($val['question_id']) {
					$posts_list[$key]['question_is_focus'] = $this->model('question')->has_focus_question($val['question_id'], $this->user_id);
				}

				if ($val['answer_count'])
				{
					$posts_list[$key]['answer_users'] = $this->model('question')->get_answer_users_by_question_id($val['question_id'], 2, $val['published_uid']);
				}

				if ($val['comments'])
				{
					$posts_list[$key]['comments_info'] = $this->model('article')->get_comments($val['id'], 1, 10);
					// var_dump($posts_list[$key]);
				}
			}
		}

		TPL::assign('pagination', AWS_APP::pagination()->initialize(array(
			'base_url' => get_js_url('/sort_type-' . preg_replace("/[\(\)\.;']/", '', $_GET['sort_type']) . '__category-' . $category_info['id'] . '__day-' . intval($_GET['day']) . '__is_recommend-' . intval($_GET['is_recommend'])),
			'total_rows' => $this->model('posts')->get_posts_list_total(),
			'per_page' => get_setting('contents_per_page')
		))->create_links());

		TPL::assign('posts_list', $posts_list);
		TPL::assign('posts_list_bit', TPL::output('explore/ajax/list', false));
		TPL::assign('posts_his_list_bit', TPL::output('explore/ajax/his_list', false));

		TPL::output('explore/index');
	}
}
