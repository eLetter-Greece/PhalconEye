<?php

class Page extends \Phalcon\Mvc\Model
{

    /**
     * @var int
     *
     */
    protected $id;

    /**
     * @var string
     *
     */
    protected $title;

    /**
     * @var string
     *
     */
    protected $url;

    /**
     * @var string
     * @form_type textArea
     *
     */
    protected $description;

    /**
     * @var string
     * @form_type textArea
     *
     */
    protected $keywords;

    /**
     * @var string
     * @form_type selectStatic
     *
     */
    protected $layout = 'middle';

    /**
     * @var string
     *
     */
    protected $controller = null;

    /**
     * @var int
     *
     */
    protected $view_count = 0;


    /**
     * Method to set the value of field id
     *
     * @param int $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * Method to set the value of field title
     *
     * @param string $title
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }

    /**
     * Method to set the value of field url
     *
     * @param string $url
     */
    public function setUrl($url)
    {
        $this->url = $url;
    }

    /**
     * Method to set the value of field description
     *
     * @param string $description
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }

    /**
     * Method to set the value of field keywords
     *
     * @param string $keywords
     */
    public function setKeywords($keywords)
    {
        $this->keywords = $keywords;
    }

    /**
     * Method to set the value of field layout
     *
     * @param string $layout
     */
    public function setLayout($layout)
    {
        $this->layout = $layout;
    }

    /**
     * Method to set the value of field controller
     *
     * @param string $controller
     */
    public function setController($controller)
    {
        $this->controller = $controller;
    }

    /**
     * Method to set the value of field view_count
     *
     * @param int $view_count
     */
    public function setViewCount($view_count)
    {
        $this->view_count = $view_count;
    }


    /**
     * Returns the value of field id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Returns the value of field title
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Returns the value of field url
     *
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * Returns the value of field description
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Returns the value of field keywords
     *
     * @return string
     */
    public function getKeywords()
    {
        return $this->keywords;
    }

    /**
     * Returns the value of field layout
     *
     * @return string
     */
    public function getLayout()
    {
        return $this->layout;
    }

    /**
     * Returns the value of field controller
     *
     * @return string
     */
    public function getController()
    {
        return $this->controller;
    }

    /**
     * Returns the value of field view_count
     *
     * @return int
     */
    public function getViewCount()
    {
        return $this->view_count;
    }

    public function setWidgets($widgets = array())
    {
        if (!$widgets)
            $widgets = array();

        // updating
        $existing_widgets = $this->getWidgets();
        $widgets_ids_to_remove = array(); // widgets that we need to remove
        // looping all exisitng widgets and looping new widgets
        // looking for new, changed, and deleted actions
        /** @var Content $ex_widget */
        foreach ($existing_widgets as $ex_widget) {
            $founded = false; // indicates if widgets founded in new array
            $orders = array();

            foreach ($widgets as $item) {
                if (empty($orders[$item["layout"]]))
                    $orders[$item["layout"]] = 1;
                else
                    $orders[$item["layout"]]++;

                if ($ex_widget->getId() == $item["id"]) {
                    $ex_widget->setLayout($item["layout"]);
                    $ex_widget->setWidgetOrder($orders[$item["layout"]]);
                    $ex_widget->setParams($item["params"]);
                    $ex_widget->save();
                    $founded = true;
                }
            }

            if (!$founded) {
                $widgets_ids_to_remove[] = $ex_widget->getId();
            }
        }

        // inserting
        $orders = array();
        foreach ($widgets as $item) {
            if (empty($orders[$item["layout"]]))
                $orders[$item["layout"]] = 1;
            else
                $orders[$item["layout"]]++;

            if ($item["id"] == 0) { // need to be inserted
                $content = new Content();
                $content->setPageId($this->id);
                $content->setWidgetId($item["widget_id"]);
                $content->setLayout($item["layout"]);
                $content->setParams($item["params"]);
                $content->setWidgetOrder($orders[$item["layout"]]);
                $content->save();
            }
        }

        if (!empty($widgets_ids_to_remove)) {
            $rowsToRemove = Content::find("id IN (" . implode(',', $widgets_ids_to_remove).")");
            $rowsToRemove->delete();
        }




    }

    public function getWidgets($cache = true)
    {
        $config = $this->getDI()->get('config');

        $data = array(
            "page_id = '{$this->id}'",
            "order" => "widget_order",

        );

        if ($cache){
            $data['cache'] = array(
                'key' => "page_{$this->id}_widgets.cache",
                'lifetime' => ($config->application->debug ? 0 : 86400) // 1 day
            );
        }

        return Content::find($data);
    }

    public function getSource()
    {
        return "pages";
    }

    public function validation()
    {
        $this->validate(new \Phalcon\Mvc\Model\Validator\StringLength(array(
            "field" => "url",
            'min' => 1
        )));

        $this->validate(new \Phalcon\Mvc\Model\Validator\PresenceOf(array(
            'field' => 'title'
        )));

        $this->validate(new \Phalcon\Mvc\Model\Validator\Uniqueness(array(
            'field' => 'url'
        )));


        if ($this->validationHasFailed() == true) {
            return false;
        }
    }

}