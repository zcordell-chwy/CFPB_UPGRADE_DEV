<?php
class Prodcat_model extends Model
{
    private static $arrayOfProductHierLevels;

    function __construct()
    {
        parent::__construct();
    }

    /**
    * Returns an array of all hierarchy items and sub-items for the specified level.
    * @param $filterType string products or categories
    * @param $level int hierarchy level
    * @return array of hierarchy items : (id, label, seq, parent, level, hierList, subItems)
    */
    public function getHierItemsForLevel($filterType, $level)
    {
        define('queryLimit', 2);

        $cacheKey = $filterType;
        if($cachedResult = checkCache($cacheKey))
        {
            $cachedResult = $cachedResult;
            if($level < queryLimit)
            {
                //the entire hierarchy's been cached: pare off what isn't needed
                foreach($cachedResult as $key => $value)
                {
                    unset($cachedResult[$key]['subItems']);
                }
            }
            return $cachedResult;
        }

        if(strstr($filterType, 'prod') !== false)
            $filterType = HM_PRODUCTS;
        else if(strstr($filterType, 'cat') !== false)
            $filterType = HM_CATEGORIES;
        else 
            return false;

        //build up the hierarchy query
        $nextLevel = queryLimit + 1;
        $levelQuery = $orderQuery = '';
        define('origSelectSize', 3);    //three original columns in following select statement
        for($i = 1; $i < $nextLevel; $i++)
        {
            $levelQuery .= ", h.lvl{$i}_id";
            $orderQuery .= ', ' . ($i + origSelectSize);
        }
        $si = sql_prepare(sprintf("SELECT h.id, l.label, h.seq $levelQuery FROM labels l, hier_menus h, visibility v
                          WHERE (l.tbl=%d) AND (l.lang_id=%d) AND (l.fld=1) AND (l.label_id=h.id)
                          AND (h.hm_type=$filterType) AND (h.lvl{$nextLevel}_id IS NULL)
                          AND (v.interface_id = %d) AND (v.tbl=%d) AND (v.id=h.id) AND (v.enduser = 1)
                           ORDER BY 3, 1 {$orderQuery}", TBL_HIER_MENUS, lang_id(LANG_DIR), intf_id(), TBL_HIER_MENUS));

        sql_bind_col($si, 1, BIND_INT, 0); //id
        sql_bind_col($si, 2, BIND_NTS, 241); //label
        sql_bind_col($si, 3, BIND_INT, 0); //seq

        for($i = 1; $i < $nextLevel; $i++)
        {
            $bindNumber = $i + origSelectSize;
            sql_bind_col($si, $bindNumber, BIND_INT, 0);    //ea. subsequent level's id
        }

        $results = $topLevel = array();
        for($i = 0; $row = sql_fetch($si); $i++)
        {
            if($row[0] === $row[3])
            {
                $topLevel[$row[0]] = array('id' => $row[0], 'label' => htmlspecialchars($row[1], ENT_QUOTES, 'UTF-8'), 'seq' => $row[2], 'parent' => $row[3], 'level' => 0, 'hierList' => $row[0], 'subItems' => array());
            }
            else
            {
                switch($row[0])
                {
                    case($row[4]):
                        $lvl = 1;
                        $parent = $row[3];
                        $hierList = $row[3] .','. $row[4];
                        break;
                    case ($row[5]):
                        $lvl = 2;
                        $parent = $row[4];
                        $hierList = $row[3] .','. $row[4] . ',' . $row[5];
                        break;
                    case($row[6]):
                        $lvl = 3;
                        $parent = $row[5];
                        $hierList = $row[3] .','. $row[4] . ',' . $row[5] . ',' . $row[6];
                        break;
                    case($row[7]):
                        $lvl = 4;
                        $parent = $row[6];
                        $hierList = $row[3] .','. $row[4] . ',' . $row[5] . ',' . $row[6] . ',' . $row[7];
                        break;
                    case($row[8]):
                        $lvl = 5;
                        $parent = $row[7];
                        $hierList = $row[3] .','. $row[4] . ',' . $row[5] . ',' . $row[6] . ',' . $row[7] . ',' . $row[8];
                        break;
                }
                $results[$i] = array('id' => $row[0], 'label' => $row[1], 'seq' => $row[2], 'parent' => $parent, 'level' => $lvl, 'hierList' => $hierList);
            }
        }
        sql_free($si);
        //group children nodes by their parent
        $childrenGrouping = array();
        foreach($results as $hierNode)
        {
            if(!is_array($childrenGrouping[$hierNode['parent']]))
                $childrenGrouping[$hierNode['parent']] = array();
            array_push($childrenGrouping[$hierNode['parent']], $hierNode);
        }
        //sort ea. group of children and add them into the top-level array
        foreach($childrenGrouping as $parentID => $child)
        {
            //sort the children groupings according to their seq number
            uasort($child, function($a, $b) { if($a["seq"] === $b["seq"]) return 0; return ($a["seq"] < $b["seq"]) ? -1 : 1; });
            //combine the top-level parent and the children array and then add to complete result set
            if($topLevel[$parentID])
                $topLevel[$parentID]['subItems'] = $childrenGrouping[$parentID];
        }
        setCache($cacheKey, $topLevel);

        if($level < queryLimit)
        {
            //the entire hierarchy's cached: pare off what isn't needed
            foreach($topLevel as $key => $value)
            {
                unset($topLevel[$key]['subItems']);
            }
        }
        return $topLevel;
    }

    /**
     * This function gets values out of the hier_menus table for all the children
     * of the passed in ID for the specified level
     *
     * @param $filterType string What type of hier_menu to get_browser
     * @param $level int What level of hier_menu items to get (1-6)
     * @param $id int The parent id from which to get the child items
     * @param $linking int If product linking is on
     *
     * @return array Results from sql call
     */
    function hierMenuGet($filterType, $level, $id, $linking)
    {
        $hierMenuType = (stringContains($filterType, 'prod')) ? HM_PRODUCTS : HM_CATEGORIES;
        $level = min(6, max(1, (int)$level));
        $parentLevel = $level - 1;
        $childLevel = $level + 1;
        $grandchildLevel = $childLevel + 1;
        $languageID = lang_id(LANG_DIR);
        $interfaceID = intf_id();
        $hierMenuTableID = TBL_HIER_MENUS;
        $id = (int)$id;

        if ($level === 6) 
        {
            $childCountQuery = '0 as childCount';
        }
        else 
        {
            // I tried to wrap this subselect in an if() that would only cause it to run if the leaf column indicated the node was internal.
            // But somehow, freakishly, that was slower.  I don't understand.
            $childCountQuery = "(SELECT count(childVisibility.id) > 0
                FROM hier_menus child 
                LEFT OUTER JOIN visibility childVisibility on childVisibility.id = child.id AND childVisibility.tbl = $hierMenuTableID AND childVisibility.interface_id = $interfaceID AND childVisibility.enduser = 1
                WHERE h.id = child.lvl{$level}_id AND child.id = child.lvl{$childLevel}_id) childCount";
        }

        $parentClause = ($level > 1) ? "AND h.lvl{$parentLevel}_id = $id" : "";
        $sql = 
            "SELECT h.id, l.label, h.seq, $childCountQuery
            FROM hier_menus h 
            JOIN labels l ON l.label_id = h.id AND l.tbl = $hierMenuTableID AND l.lang_id = $languageID AND l.fld = 1 
            JOIN visibility v ON v.id = h.id AND v.interface_id = $interfaceID AND v.tbl = $hierMenuTableID AND v.enduser = 1 
            WHERE h.hm_type = $hierMenuType AND h.id=h.lvl{$level}_id $parentClause
            ORDER BY h.seq, h.id, l.label";

        $si = sql_prepare($sql);
        $i = 0;
        sql_bind_col($si, ++$i, BIND_INT, 0);
        sql_bind_col($si, ++$i, BIND_NTS, 241);
        sql_bind_col($si, ++$i, BIND_INT, 0);
        sql_bind_col($si, ++$i, BIND_INT, 0);

        $results = array();
        while ($row = sql_fetch($si))
        {
            $row[1] = htmlspecialchars($row[1], ENT_QUOTES, 'UTF-8');
            $results[]= $row;
        }
        sql_free($si);

        if($linking && $hierMenuType === HM_PRODUCTS) // You can't do linking from categories to products.  It only goes from product to categories.
            $linkMap = $this->hierMenuGetLinking($id);
        else 
            $linkMap = array();

        return array(
            0 => $results,
            'link_map' => $linkMap,
        );
    }

   /**
     * Given a product ID, this function will return the linked categories.  The first element in
     * the returned array will be the level of the category.
     *
     * @param $id int The product ID
     *
     * @return array The generated link map
     */
    function hierMenuGetLinkingWithLevel($id)
    {
        $linkMap = array(array());
        $languageID = lang_id(LANG_DIR);
        $table = TBL_HIER_MENUS;
        $interfaceID = intf_id();
        
        //Only grab items out of prod links if an actual ID was selected....
        if($id > 0)
        {
            $categoryIDs = $this->getCategoriesLinkedTo($id);
            if (!$categoryIDs)
                return $linkMap;

            $si = sql_prepare("SELECT FIELD(h.id, h.lvl1_id, h.lvl2_id, h.lvl3_id, h.lvl4_id, h.lvl5_id, h.lvl6_id) AS level,
            			h.id,
                        IFNULL(ELT(FIELD(h.id, h.lvl1_id, h.lvl2_id, h.lvl3_id, h.lvl4_id, h.lvl5_id, h.lvl6_id) - 1, h.lvl1_id, h.lvl2_id, h.lvl3_id, h.lvl4_id, h.lvl5_id), 0) AS parent,
                        l1.label 
                    FROM hier_menus h
                    JOIN visibility v1 ON (v1.tbl = $table) AND (v1.interface_id = $interfaceID) AND (v1.id = h.id)
                    JOIN labels l1 ON (l1.tbl = $table) AND (l1.lang_id = $languageID) AND (l1.fld = 1) AND (l1.label_id = h.id)
                    WHERE v1.enduser = 1 AND h.id IN ($categoryIDs)
                    ORDER BY field(h.id, h.lvl1_id, h.lvl2_id, h.lvl3_id, h.lvl4_id, h.lvl5_id, h.lvl6_id) DESC, h.seq");

            $i = 0;
            sql_bind_col($si, ++$i, BIND_INT, 0);   // level
            sql_bind_col($si, ++$i, BIND_INT, 0);   // id
            sql_bind_col($si, ++$i, BIND_INT, 0);   // parent's id
            sql_bind_col($si, ++$i, BIND_NTS, 241); // label

            $linkMap = array();
            $invisibleParents = array();
            while ($row = sql_fetch($si)) 
            {
                list($level, $id, $parentID, $label) = $row;
                $label = htmlspecialchars($label, ENT_QUOTES, 'UTF-8');
                $hasChildren = (int) array_key_exists($id, $linkMap);

                $linkMap[$parentID][] = array($id, $label, $hasChildren, $level);

                if ($hasChildren) 
                    unset($invisibleParents[$id]);
                if ($parentID)
                    $invisibleParents[$parentID] = true;
            }

            // For some reason (later explained by Duane as "It's allowable because it was easier than 
            // adding the checking to prevent it."  Guess where the checking to prevent it is!  Below.), 
            // it's possible to have products which link to visible categories which 
            // are parented by an invisible category.  It's easy enough to write a query to avoid that,
            // but avoiding the visible great grandchild of an invisible category is harder.  Consequently,
            // I wrote this code which finds nodes which aren't parented by a visible level 1 node and
            // deletes them and all their descendents from the link map.
            $invisibleParents = array_keys($invisibleParents);
            while ($invisibleParent = array_pop($invisibleParents)) 
            {
                if (is_array($linkMap[$invisibleParent])) 
                {
                    foreach ($linkMap[$invisibleParent] as $childRow)
                    {
                        $invisibleParents[]= $childRow[0];
                    }
                    unset($linkMap[$invisibleParent]);
                }
            }
        }
        //...otherwise just grab top level items
        else if($id == -1)
        {
            $categories = HM_CATEGORIES;
            $si = sql_prepare("SELECT FIELD(h.id, h.lvl1_id, h.lvl2_id, h.lvl3_id, h.lvl4_id, h.lvl5_id, h.lvl6_id) AS level,
            		h.id, l.label, h.leaf IS NULL 
                  FROM labels l, hier_menus h, visibility v
                  WHERE (l.tbl = $table) AND (l.lang_id = $languageID) AND (l.fld = 1) AND (l.label_id = h.id)
                  AND (h.hm_type = $categories) AND (h.lvl1_id IS NOT NULL) AND (h.lvl2_id IS NULL)
                  AND (v.interface_id = $interfaceID) AND (v.tbl = $table) AND (v.id = h.id) AND (v.enduser = 1)
                  ORDER BY h.seq, h.id, l.label");
            $i = 0;
            sql_bind_col($si, ++$i, BIND_INT, 0);   // level
            sql_bind_col($si, ++$i, BIND_INT, 0);   // id
            sql_bind_col($si, ++$i, BIND_NTS, 241); // label
            sql_bind_col($si, ++$i, BIND_INT, 0);   // leaf (1 - has children, 0 - has no children)

            while ($row = sql_fetch($si))
            {
                list($level, $id, $label, $hasChildren) = $row;
                $label = htmlspecialchars($label, ENT_QUOTES, 'UTF-8');
                $linkMap[0][] = array($id, $label, $hasChildren, $level);
            }
        }
        sql_free($si);
        // Make sure we send something back
        if(!$linkMap[0])
            $linkMap[0] = array();
        return $linkMap;
    }

    /**
     * Given a product ID, this function will return the linked categories
     *
     * @param $id int The product ID
     *
     * @return array The generated link map
     */
    function hierMenuGetLinking($id)
    {
        $originalLinkMap = $this->hierMenuGetLinkingWithLevel($id);
        $linkMap = array();

        foreach ($originalLinkMap as $parentID => $children)
        {
            $linkMap[$parentID] = array();
            foreach ($children as $child)
            {
                //Take off the level item to keep data consistent
                array_pop($child);
                $linkMap[$parentID][] = $child;
            }
        }
        return $linkMap;
    }

    /**
     * Function to return if prod/cat linking is turned on. Only checks
     * if the filter is of type product.
     *
     * @return bool True of linking is turned on, false otherwise
     */
    static function getLinkingMode()
    {
        return CFG_OPT_PROD_CAT_LINK & sci_cache_int_get(SCI_OPTS);
    }

    /**
     * Returns the enduser visibility field from the DB given the ID to
     * a product or category
     * @return Int Either 0 if ID is not visible or 1 if it is
     * @param $id int The ID of the product or category
     */
    function getEnduserVisibility($id)
    {
        return sql_get_int(sprintf('SELECT enduser FROM visibility WHERE interface_id = %d AND tbl = %d AND id = %d', intf_id(), TBL_HIER_MENUS, $id));
    }

    /**
     * Returns the portion of a product or category's hierarchy chain which is visible
     * to end users. For example, if the chain is 1,2,3 (where 1 is the root node and
     * 3 is the leaf node) and 2 is end user visible, but 3 is not, then this function
     * will return array(1,2).
     * @return Array The enduser visible hierarchy
     * @param $hierarchyArray Array The hierarchy of product or category IDs to check
     */
    function getEnduserVisibleHierarchy($hierarchyArray)
    {
        $hierarchyArray = array_filter($hierarchyArray, 'trim');
        $hierarchyArrayLength = count($hierarchyArray);
        if (!$hierarchyArrayLength)
            return array();

        for ($i = 0; $i < $hierarchyArrayLength; $i++)
        {
            $valueToTest = $hierarchyArray[$i];
            // if any value is suspect (i.e. not a good integer),
            // just return nothing
            // profile values are already an integer array, which is why we
            // also do a strval() on $valueToTest
            // profile values are already an integer array, which is why we
            // also do a strval() on $valueToTest
            if (strval(intval($valueToTest, 10)) !== strval($valueToTest))
                return array();
        }

        $CI = get_instance();
        $si = sql_prepare(sprintf('SELECT id, enduser FROM visibility WHERE interface_id = %d AND tbl = %d AND id IN (%s)',
            intf_id(), TBL_HIER_MENUS, strtr(implode(",", $hierarchyArray), $CI->rnow->getSqlEscapeCharacters())));
        $i = 0;
        sql_bind_col($si, ++$i, BIND_INT, 0);
        sql_bind_col($si, ++$i, BIND_INT, 0);
        $visibility = array();
        while ($row = sql_fetch($si))
        {
            $visibility[$row[0]] = $row[1];
        }
        sql_free($si);

        for($i = 0; $i < $hierarchyArrayLength; $i++)
        {
            $id = $hierarchyArray[$i];
            if(!$visibility[$id])
            {
                return array_slice($hierarchyArray, 0, $i);
            }
        }
        return $hierarchyArray;
    }

    /**
    * Returns a (static) default array of product IDs
    * @return array Previously set value or null if hitherto hasn't been set
    */
    function getDefaultProduct()
    {
        return self::$arrayOfProductHierLevels;
    }

    /**
    * Sets a (static) default array of product IDs
    * @param $newDefault array Product IDs. (one ID per hierarchy level)
    */
    function setDefaultProduct($newDefault)
    {
        self::$arrayOfProductHierLevels = $newDefault;
    }

    /**
     * Database call to get list of all hier_menu items of a specific
     * type in sorted order.
     *
     * @param $hmType int The type of hier menu item to retrieve
     * @param $linkingValue int[optional] Value of current product selected to get linking values
     *
     * @return array The results of the sql call in sorted order
     */
    function getHierPopup($hmType, $linkingValue = null)
    {
        $hmType = intval($hmType);
        if($hmType === 0)
            return array(); 
        $linkingValue = intval($linkingValue);
        $langID = lang_id(LANG_DIR);
        if($linkingValue > 0)
        {
            //We have to get the chain of the ID passed in first
            $si = sql_prepare("select lvl1_id, lvl2_id, lvl3_id, lvl4_id, lvl5_id, lvl6_id from hier_menus where id = $linkingValue");

            sql_bind_col($si, 1, BIND_INT, 0);
            sql_bind_col($si, 2, BIND_INT, 0);
            sql_bind_col($si, 3, BIND_INT, 0);
            sql_bind_col($si, 4, BIND_INT, 0);
            sql_bind_col($si, 5, BIND_INT, 0);
            sql_bind_col($si, 6, BIND_INT, 0);
            $productChainArray = array();
            $row = sql_fetch($si);
            for($i=0; $i<6; $i++)
            {
                if($row[$i])
                    array_push($productChainArray, $row[$i]);
            }
            sql_free($si);
            $productChain = implode(",", $productChainArray);

            $categoryIDs = $this->getCategoriesLinkedTo($linkingValue);
            if (!$categoryIDs)
                return array();

            $si = sql_prepare(sprintf("select l1.label, h.id, h.seq, h.lvl1_id, h.lvl2_id, h.lvl3_id, h.lvl4_id, h.lvl5_id, h.lvl6_id, l1d.label
                                       from hier_menus h
                                       left outer join visibility v1
                                           on (v1.tbl = %d) and (v1.interface_id = %d) and (v1.id = h.id)
                                       left outer join labels l1
                                           on (l1.tbl = %d) and (l1.lang_id = $langID) and (l1.fld = 1) and (l1.label_id = h.id)
                                       left outer join labels l1d
                                           on (l1d.tbl = %d) and (l1d.lang_id = $langID) and (l1d.fld = 2) and (l1d.label_id = h.id)
                                       where v1.enduser = 1 AND h.hm_type = %d AND h.id in ($categoryIDs)", TBL_HIER_MENUS, intf_id(), TBL_HIER_MENUS, TBL_HIER_MENUS, HM_CATEGORIES));
        }
        else
        {
            $si = sql_prepare(sprintf("select l1.label, t1.id, t1.seq, t1.lvl1_id, t1.lvl2_id, t1.lvl3_id, t1.lvl4_id, t1.lvl5_id, t1.lvl6_id, l1d.label
                                       from hier_menus t1
                                       left outer join visibility v1
                                        on (v1.tbl = %d) and (v1.interface_id = %d) and (v1.id = t1.id)
                                       left outer join labels l1
                                        on (l1.tbl = %d) and (l1.lang_id = $langID) and (l1.fld = 1) and (l1.label_id = t1.id)
                                       left outer join labels l1d
                                        on (l1d.tbl = %d) and (l1d.lang_id = $langID) and (l1d.fld = 2) and (l1d.label_id = t1.id)
                                       where (v1.enduser = 1) AND t1.hm_type = $hmType", TBL_HIER_MENUS, intf_id(), TBL_HIER_MENUS, TBL_HIER_MENUS));
        }

        sql_bind_col($si, 1, BIND_NTS, 80);   define(label1, 0);
        sql_bind_col($si, 2, BIND_INT, 0);    define(id, 1);
        sql_bind_col($si, 3, BIND_INT, 0);    define(seq, 2);
        sql_bind_col($si, 4, BIND_INT, 0);    define(lvl1_id, 3);
        sql_bind_col($si, 5, BIND_INT, 0);    define(lvl2_id, 4);
        sql_bind_col($si, 6, BIND_INT, 0);    define(lvl3_id, 5);
        sql_bind_col($si, 7, BIND_INT, 0);    define(lvl4_id, 6);
        sql_bind_col($si, 8, BIND_INT, 0);    define(lvl5_id, 7);
        sql_bind_col($si, 9, BIND_INT, 0);    define(lvl6_id, 8);
        sql_bind_col($si, 10, BIND_NTS, 4001); define(desc, 9);

        $results = array();
        for($i=0; $row = sql_fetch($si); $i++)
        {
                if ($row[id] == $row[lvl1_id])
                    $row['level'] = 0;
                elseif ($row[id] == $row[lvl2_id])
                    $row['level'] = 1;
                elseif ($row[id] == $row[lvl3_id])
                    $row['level'] = 2;
                elseif ($row[id] == $row[lvl4_id])
                    $row['level'] = 3;
                elseif ($row[id] == $row[lvl5_id])
                    $row['level'] = 4;
                else
                    $row['level'] = 5;

            $row[0] = htmlspecialchars($row[0], ENT_QUOTES, 'UTF-8');
            $results[$i] = $row;
        }
        sql_free($si);
        $tlevel = array();
        //Create array of all top level items
        foreach ($results as $key => $value)
        {
            if($value['level'] == 0)
                array_push($tlevel, $value);
        }

        usort($tlevel, array($this, "sortSequence"));
        $seqSort = array();
        for($i=0; $i<count($tlevel); $i++)
        {
            array_push($seqSort, $tlevel[$i]);
            $this->prodCatSort($tlevel[$i], 1, $seqSort, $results);
        }

        for($i=0; $i<count($seqSort); $i++)
        {
            for($j=3; $j<9; $j++)
            {
                if($seqSort[$i][$j+1] != "")
                {
                    $seqSort[$i]['hier_list'] .= $seqSort[$i][$j] . ",";
                }
                else
                {
                    $seqSort[$i]['hier_list'] .= $seqSort[$i][$j];
                    break;
                }
            }

            if(is_array($seqSort[$i]))
            {
                $cleansedEntry = array();
                foreach($seqSort[$i] as $key => $value)
                    if(!is_null($value))
                        $cleansedEntry[$key] = $value;
                
                $seqSort[$i] = $cleansedEntry;
            }
        }
        //Pass back product chain if set
        if($productChain)
            $seqSort['prod_chain'] = $productChain;

        return $seqSort;
    }

   /**
     * This function converts the level.id format of hier menu information into the expected
     * hier menu chain that is used everywhere else.
     * @return an array of columns or false
     * @param $level int The level of the hier menu item
     * @param $id int The id of the hier menu item
     * @param $filterType string Filter name of the hier menu item
     */
    function convertToChain($level, $id, $filterType)
    {
        if(strstr($filterType, 'prod'))
            $hmType = 13;
        else if(strstr($filterType, "cat"))
            $hmType = 14;

        // need for views engine
        if(stripos($id, "u") === 0)
        {
            $id = substr($id, 1);
            $level--;
        }

        for($i=1; $i<=$level; $i++)
        {
            if($i+1 > $level)
                $levelQuery .= 'hm.lvl'.$i.'_id';
            else
                $levelQuery .= 'hm.lvl'.$i.'_id, ';
        }

        $si = sql_prepare(sprintf("select $levelQuery from hier_menus hm
                                   left outer join visibility v1
                                   on (v1.tbl = %d) and (v1.interface_id = %d) and (v1.id = hm.id)
                                  where (v1.enduser = 1) AND hm.hm_type = $hmType AND hm.id = $id", TBL_HIER_MENUS, intf_id(), TBL_HIER_MENUS));
        for($i=1; $i<=$level; $i++)
            sql_bind_col($si, $i, BIND_INT, 0);

        $row = sql_fetch($si);
        sql_free($si);
        return $row;
    }

/********************************************************
*
* ProdCat Model Private Utility Functions
*
*********************************************************/

    /**
     * Generic sorting function used to compare sequences of hier menus
     *
     * @param array $a The first item to sort on
     * @param array $b The second item to sort on
     *
     * @return int Value denoting which item was larger (0:equal -1:less +1:greater)
     */
    private static function sortSequence($a, $b)
    {
        if ($a[seq] == $b[seq])
            return 0;
        return ($a[seq] < $b[seq]) ? -1 : 1;
    }

    /**
     * Sorting function that organizes hier menu data
     *
     * @param array $parent The current hier_menu parent
     * @param int $level The current hier_menu level
     * @param array $sortedData Hier menu data array that's being constructed
     * @param array $data Full hier menu data array
     */
    private function prodCatSort($parent, $level, &$sortedData, &$data)
    {
        $children = array();
        $hasChildren = false;
        $searchLevel = $level + 2;

        foreach($data as $value)
        {
            if(($value['level'] === ($level)) && ($value[$searchLevel] === $parent[$searchLevel]))
            {
                array_push($children, $value);
                $hasChildren = true;
            }
        }
        if($hasChildren)
        {
            usort($children, array($this, 'sortSequence'));
            $level++;
            foreach($children as $newParent)
            {
                array_push($sortedData, $newParent);
                $this->prodCatSort($newParent, $level, $sortedData, $data);
            }
        }
    }


    private function getCategoriesLinkedTo($id)
    {
        $level = sql_get_int("SELECT field(id, lvl1_id, lvl2_id, lvl3_id, lvl4_id, lvl5_id, lvl6_id) AS level FROM hier_menus WHERE id = $id");
        if (!$level)
            return false;

        $sql = "SELECT DISTINCT category.lvl1_id, category.lvl2_id, category.lvl3_id, category.lvl4_id, category.lvl5_id, category.lvl6_id 
            FROM hier_menus as category 
            JOIN prod_links ON category.id = prod_links.id 
            JOIN hier_menus as product ON product.id = prod_links.prod_id 
            WHERE product.lvl{$level}_id = {$id} AND product.leaf = 1";
        $si = sql_prepare($sql);

        $i = 0;
        sql_bind_col($si, ++$i, BIND_INT, 0);
        sql_bind_col($si, ++$i, BIND_INT, 0);
        sql_bind_col($si, ++$i, BIND_INT, 0);
        sql_bind_col($si, ++$i, BIND_INT, 0);
        sql_bind_col($si, ++$i, BIND_INT, 0);
        sql_bind_col($si, ++$i, BIND_INT, 0);

        $categoryIDs = array();
        while ($row = sql_fetch($si)) 
        {
            foreach ($row as $categoryID) 
            {
                if (!$categoryID)
                    break;
                $categoryIDs[$categoryID] = true;
            }
        }
        sql_free($si);
        return implode(',', array_keys($categoryIDs));
    }
}
