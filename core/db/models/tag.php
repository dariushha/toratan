<?php
namespace core\db\models;

class tag extends baseModel
{
    static $has_many = array(
        array('note_tags', "class_name" => "note_tag"),
        array('notes', 'through' => 'note_tags', "order" => "notes.popularity DESC")
    );
    public function save($validate=true)
    {
        $this->tag_value = ucwords(trim($this->tag_value));
        if(!strlen($this->tag_value))
            throw new \zinux\kernel\exceptions\invalidArgumentException("Tag's Value cannot be empty!");
        parent::save($validate);
    }
    /**
     * Seach tags that is matched with passed tag's value
     * @param string $tag The tag value
     * @return tag  The tag or NULL if not existed
     */
    public static function search($tag) {
        $builder = self::getSQLBuilder();     
        $builder->select("*")->where("`tag_value` = ?", array($tag))->limit("1");
        return array_shift(self::find_by_sql($builder->to_s(), $builder->bind_values()));
    }
    /**
     * Seach tags that is similar with passed tag's value
     * @param string $tag The tag value
     * @return array of Tags
     */
    public static function search_similar($tag) {
        $builder = self::getSQLBuilder();     
        $builder->select("*")->where("`tag_value` LIKE ?", array("%$tag%"))->limit("14");
        return self::find_by_sql($builder->to_s(), $builder->bind_values());
    }
    /**
     * Create new tag, if already existed? it will be ignored
     * @param string $tag The tag value
     * @return tag (READONLY) of created or already existed tag.
     */
    public static function create($tag) {
        $t = new self;
        try {
            $t->tag_value = $tag;
            $t->save();
        } catch(\core\db\exceptions\alreadyExistsException $aee) {
            unset($aee);
            $t = self::first(array("conditions" => array("tag_value = ?", $tag)));
        }
        $t->readonly();
        return $t;
    }
    /**
     * Fetch related notes that are bind to this tag
     * @param integer $offset The query offset
     * @param integer $limit The query limit(default: 10)
     * @param string $order The query order(default: `popularity DESC`)
     * @return array of array(count_of_all_notes_with_this_tag, current_result_due_to_offset_and_limit)
     */
    public function fetch_related_notes($offset, $order = "popularity DESC", $limit = 10, $is_public = note::FLAG_SET) {
        $tags = note_tag::all(array("conditions" => array("tag_id = ?", $this->tag_id), "select" => "note_id"));
        $in = "";
        foreach($tags as $tag) {
            $in = "$in, '$tag->note_id'";
        }
        $in_val = trim($in, ", ");
        $builder = new \ActiveRecord\SQLBuilder(note::connection(), note::table_name());
        $cond = array("note_id IN ($in_val)");
        switch($is_public) {
            case note::FLAG_SET:
            case note::FLAG_UNSET:
                $cond[0] .= " AND is_public = ?";
                $cond[] = $is_public;
                break;
        }
        $builder->select("*");
        call_user_func_array(array($builder, "where"), $cond);
        $builder->limit($limit)->offset($offset)->order($order);
        $notes = note::find_by_sql($builder->to_s(), $builder->bind_values());
        $builder = new \ActiveRecord\SQLBuilder(note::connection(), note::table_name());
        $builder->select("count(*) as total_count");
        call_user_func_array(array($builder, "where"), $cond);
        $count = @array_shift(note::find_by_sql($builder->to_s(), $builder->bind_values()))->total_count;
        if(!$count)
            $count = 0;
        return array($count, $notes);
    }
}