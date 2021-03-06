<?php
namespace core\db\models;
/**
 * Note Entity
 */
class note extends item
{
    static $has_many = array(
        array('note_tags', "class_name" => "note_tag"),
        array('tags', 'through' => 'note_tags', "order" => "note_tags.created_at ASC"),
        array('comments', "order" => "comments.created_at DESC")
    );
    /**
     * General note validator
     */
    public function validate() {
        # invoke parent validator
        parent::validate();
        # validate the note's body existance
        if(!isset($this->note_body) || !\strlen($this->getItemBody()))
            $this->errors->add("{$this->item_name}'s body", "cannot be blank.");
    }
    /**
     * Saves current note into database
     * @param boolean $validate Should validate?
     */
    public function save($validate=true)
    {
        # perform a normalization on note's title and body
        list($this->note_title, $this->note_body) = self::__normalize($this->note_title, $this->note_body);
        parent::save($validate);
    }
    /**
     * Creates a new item in { title | body } datastructure
     * @param string $title the item's title
     * @param string $body the item's body
     * @param string $parent_id the item's parent id
     * @param string $owner_id the item's owner
     * @param int $editor_type The editor type ID which the note has been edited with
     * @throws \zinux\kernel\exceptions\invalidArgumentException if title not string or be empty
     * @throws \zinux\kernel\exceptions\invalidOperationException if duplication problem raise during saving item to db
     * @throws \core\db\models\Exception if any other exception raised that didn't match with previous excepions
     * @return item the create item
    */
    public function newItem(
            $title,
            $body,
            $parent_id,
            $owner_id,
            $editor_type)
    {
        if(!isset($editor_type) || !is_numeric($editor_type) || $editor_type < 0)
            throw new \zinux\kernel\exceptions\invalidArgumentException("The `editor type` should be unsigned numeric!");
        $note = parent::newItem($title, $body, $parent_id, $owner_id);
        $note->editor_type = $editor_type;
        $note->save();
        return $note;
    }
    /**
     * Edits an item
     * @param string $item_id the item's id
     * @param string $owner_id the item's owner id
     * @param string $title string the item's title
     * @param string $body the item's body
     * @param string $parent_id the item's new parent ID, pass '<b>item::NOCHANGE</b>' to don't chnage
     * @param boolean $is_public should it be public or not, pass '<b>item::NOCHANGE</b>' to don't chnage
     * @param boolean $is_trash should it be trashed or not, pass '<b>item::NOCHANGE</b>' to don't chnage
     * @param boolean $is_archive should it be archived or not, pass '<b>item::NOCHANGE</b>' to don't chnage
     * @param int $editor_type The editor type ID which the note has been edited with, if it passes the `editor_type` attribute of note will be changed, otherwise it will remaine the same.
     * @return item the edited item
     * @throws \core\db\exceptions\dbNotFoundException if the item not found
     */
    public function edit(
            $item_id,
            $owner_id,
            $title,
            $body,
            $parent_id = self::NOCHANGE,
            $is_public = self::NOCHANGE,
            $is_trash = self::NOCHANGE,
            $is_archive = self::NOCHANGE,
            $editor_type = self::NOCHANGE)
    {
        if(!is_numeric($editor_type) || $editor_type < 0)
            throw new \zinux\kernel\exceptions\invalidArgumentException("The `editor type` should be unsigned numeric!");
        $note = parent::edit($item_id, $owner_id, $title, $body, $parent_id, $is_public, $is_trash, $is_archive);
        if($editor_type != self::NOCHANGE)
            $note->editor_type = $editor_type;
        $note->save();
        return $note;
    }
    /**
     * Normalizes the note's title and body
     * @param string $title
     * @param string $body
     * @return array A normalized array of `array($title, $body)`
     */
    public static function __normalize($title, $body, $ckeditor_normalization = 0) {
        $title = ucfirst(strip_tags(trim($title)));
        $body = ucfirst(trim($body));
        if($ckeditor_normalization)
            $body = preg_replace(
                                array(
                                        "#(?:<br\b[^>]*>|\R){1,}#i",
                                        "#<p\b[^>]*>(&nbsp;)?</p>#i"
                                ), array(
                                        "<br />\n",
                                        "",
                                ), $body);
        return array($title, $body);
    }
    /**
     * Applies summary to current note
     * @param string $summary
     * @param boolean $auto_save (default: true) Shoud the method attempt to autosave after the assignment?
     * @return \core\db\models\note $this
     */
    public function apply_summary($summary, $auto_save = 1) {
        $this->note_summary = $summary;
        if($auto_save)
            $this->save();
        return $this;
    }
    /**
     * Applies note pre-processed html body to current note
     * @param string $html
     * @param boolean $auto_save (default: true) Shoud the method attempt to autosave after the assignment?
     * @return \core\db\models\note $this
     */
    public function apply_note_html_body($html, $auto_save = 1) {
        $this->note_html_body = $html;
        if($auto_save)
            $this->save();
        return $this;
    }
    /**
     * Get value of tags which are associated with this note
     * @return array of string
     */
    public function get_tags_value(){
        $tags = array();
        $otags = $this->tags;
        foreach($otags as $tag) {
            $tags[] = $tag->tag_value;
        }
        return $tags;
    }
    /**
     * fetches related notes to current instance by match they tag value with current instace's tag value
     * @param integer $offset The query's offset
     * @param integer $limit The query's limit
     * @param string $order The order string
     * @param string $select The select columns
     * @return array The fetched notes
     */
    public function fetch_related_notes(
            $offset = 0,
            $limit = 10,
            $order = "popularity DESC, created_at DESC",
            $select  = "DISTINCT notes.note_id, note_title, note_summary, owner_id, popularity, notes.created_at, notes.updated_at") {
        # fetch current note's tag
        $tags = $this->tags;
        # the tag ID container
        $tag_ids = array();
        # collect the tag IDs
        foreach($tags as $tag) {$tag_ids[] = $tag->tag_id;}
        # invoke a sql builder
        $builder = new \ActiveRecord\SQLBuilder(self::connection(), self::table_name());
        # build the query
        $builder
                ->select($select)
                ->joins("INNER JOIN note_tags ON note_tags.note_id = notes.note_id INNER JOIN tags ON tags.tag_id = note_tags.tag_id")
                ->where("tags.tag_id IN  ({$this->escape_in_query($tag_ids)}) AND notes.note_id <> ?", $this->getItemID())
                ->order($order)
                ->offset($offset)
                ->limit($limit);
        # execute and return the query 
        return self::find_by_sql($builder->to_s(), $builder->bind_values());
    }
}