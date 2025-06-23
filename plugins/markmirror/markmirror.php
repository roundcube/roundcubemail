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
            $this->add_hook('storage_init', [$this, 'force_loading_custom_email_header']);
            $this->add_hook('message_compose_body', [$this, 'load_editor']);
        } elseif ($task == 'mail' && $action === 'send') {
            $this->add_hook('message_ready', [$this, 'save_markdown_editor_usage']);
        }
    }

    public function load_editor(array $args): array
    {
        $headers = $args['message']->headers;
        $start_markmirror = isset($headers) && $headers->get('x-edited-by-markmirror') === 'yes';
        $rcmail = rcube::get_instance();
        $rcmail->output->set_env('start_markmirror', $start_markmirror);

        // Load the editor files.
        $this->include_script('markmirror.js', ['type' => 'module']);
        $this->include_stylesheet('markmirror.css');
        $this->add_texts('localization', true);
        return $args;
    }

    public function force_loading_custom_email_header($args)
    {
        if (isset($args['fetch_headers'])) {
            $args['fetch_headers'] .= ' X-EDITED-BY-MARKMIRROR';
        } else {
            $args['fetch_headers'] = 'X-EDITED-BY-MARKMIRROR';
        }
        return $args;
    }

    public function save_markdown_editor_usage($args)
    {
        if (isset($_POST['_edited_by_markmirror']) && $_POST['_edited_by_markmirror'] === '1') {
            $message = $args['message'];
            $message->headers(['X-Edited-By-Markmirror' => 'yes'], true);
            $args['message'] = $message;
        }
        return $args;
    }
}
