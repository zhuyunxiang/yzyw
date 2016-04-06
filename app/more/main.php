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
		$rule_action['rule_type'] = "white";	// 黑名单,黑名单中的检查  'white'白名单,白名单以外的检查

		if ($this->user_info['permission']['visit_topic'] AND $this->user_info['permission']['visit_site'])
		{
			// $rule_action['actions'][] = 'square';
			$rule_action['actions'][] = 'index';
		}

		return $rule_action;
	}

    public function index_action()
    {
        if (is_mobile())
		{
			HTTP::redirect('/m/topic/' . $_GET['id']);
		}

        if ($this->user_id) {
            $topics_list = $this->model('topic')->get_topic_list();
            $focus_topics_list = $this->model('topic')->get_focus_topic_list($this->user_id, $this->user_info['topic_focus_count']);
            $focus_list = array();

            // 找出所有已经关注的话题
            foreach ($focus_topics_list as $focus_topics_list_k => $focus_topics_list_v) {
                $focus_list[$focus_topics_list_v['topic_id']] = true;
            }
            // 标记已关注的话题
            foreach ($topics_list as $topics_list_k => $topics_list_v) {
                if ($focus_list[$topics_list_v['topic_id']]) {
                    $topics_list[$topics_list_k]['is_focused'] = true;
                }
            }

            // var_dump(topics_list);
            TPL::assign('topics_list', $topics_list);
        }

        TPL::output('more/index');
    }
}
