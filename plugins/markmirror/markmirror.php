<?php

class markmirror extends rcube_plugin
{
    public $task = 'mail|settings';
    protected $rcmail;

    #[\Override]
    public function init()
    {
        $this->rcmail = rcmail::get_instance();
        $task = $this->rcmail->task;
        $action = $this->rcmail->action;
        // $section = trim(rcube_utils::get_input_string('_section', rcube_utils::INPUT_GP));

        if ($task == 'mail' && $action == 'compose') {
            $this->add_hook('message_compose_body', [$this, 'load_editor']);
        } elseif ($task == 'mail' && $action === 'send') {
            $this->add_hook('message_ready', [$this, 'save_markdown_editor_usage']);
        }
    }

    public function load_editor(array $args): array
    {
        $start_markmirror = false;
        if (isset($args['message']->headers)) {
            $draft_info = rcmail_sendmail::draftinfo_decode($args['message']->headers->get('x-draft-info'));
            $start_markmirror = $draft_info['edit_in_markmirror'] === 'yes';
        }
        $rcmail = rcube::get_instance();
        $rcmail->output->set_env('start_markmirror', $start_markmirror);

        // Load the editor files.
        $this->include_script('markmirror.min.js', ['type' => 'module']);
        $this->include_stylesheet($this->local_skin_path() . '/styles/markmirror.min.css');
        $rcmail->output->set_env('markmirror_iframe_css_path', $this->url($this->local_skin_path() . '/styles/iframe.min.css'));
        $this->add_texts('localization', true);
        return $args;
    }

    public function save_markdown_editor_usage($args)
    {
        if (isset($_POST['_edited_by_markmirror']) && $_POST['_edited_by_markmirror'] === '1') {
            $draft_info = rcmail_sendmail::draftinfo_decode($args['message']->headers()['X-Draft-Info']);
            $draft_info['edit_in_markmirror'] = 'yes';
            $args['message']->headers(['X-Draft-Info' => rcmail_sendmail::draftinfo_encode($draft_info)], true);
        }
        return $args;
    }
}
