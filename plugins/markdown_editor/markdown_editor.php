<?php

class markdown_editor extends rcube_plugin
{
    public $task = 'mail';
    protected $rcmail;

    #[\Override]
    public function init()
    {
        $this->add_hook('message_compose_body', [$this, 'load_editor']);
        $this->add_hook('message_ready', [$this, 'save_markdown_editor_usage']);
    }

    public function load_editor(array $args): array
    {
        $start_markdown_editor = false;

        if (isset($args['message']->headers)) {
            $draft_info = rcmail_sendmail::draftinfo_decode($args['message']->headers->get('x-draft-info'));
            $start_markdown_editor = $draft_info['markdown_editor'] === 'yes';
        }

        $rcmail = rcube::get_instance();
        $rcmail->output->set_env('start_markdown_editor', $start_markdown_editor);

        // Load the editor files.
        $this->include_script('markdown_editor.min.js', ['type' => 'module']);
        $this->include_stylesheet($this->local_skin_path() . '/styles/markdown_editor.min.css');
        $rcmail->output->set_env('markdown_editor_iframe_css_path', $this->url($this->local_skin_path() . '/styles/iframe.min.css'));
        $this->add_texts('localization', true);

        return $args;
    }

    public function save_markdown_editor_usage($args)
    {
        if (isset($_POST['_markdown_editor']) && $_POST['_markdown_editor'] === '1') {
            $draft_info = rcmail_sendmail::draftinfo_decode($args['message']->headers()['X-Draft-Info']);
            $draft_info['markdown_editor'] = 'yes';
            $args['message']->headers(['X-Draft-Info' => rcmail_sendmail::draftinfo_encode($draft_info)], true);
        }

        return $args;
    }
}
