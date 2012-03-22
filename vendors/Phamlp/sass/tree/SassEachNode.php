<?php

/* SVN FILE: $Id: SassEachNode.php 118 2010-09-21 09:45:11Z chris.l.yates@gmail.com $ */
/**
 * SassEachNode class file.
 * 
 * The syntax is:
 * <pre>@each <var> in <list></pre>.
 *
 * <var> is available to the rest of the script following evaluation
 * and has the value that terminated the loop.
 * 
 * @author			Chris Yates <chris.l.yates@gmail.com>
 * @author			David Neilsen <david@panmedia.co.nz>
 * @copyright       Copyright (c) 2010 PBM Web Development
 * @license			http://phamlp.googlecode.com/files/license.txt
 * @package			PHamlP
 * @subpackage      Sass.tree 
 */

/**
 * SassEachNode class.
 * Represents a Sass @each loop.
 * @package			PHamlP
 * @subpackage	Sass.tree
 */
class SassEachNode extends SassNode {
    const MATCH = '/@each\s+[!\$](\w+)\s+in\s+(.+?){?$/i';

    const VARIABLE = 1;
    const VALUES = 2;

    /**
     * @var string variable name for the loop
     */
    private $variable;
    /**
     * @var array expression that provides the list of values
     */
    private $values;

    /**
     * SassEachNode constructor.
     * @param object source token
     * @return SassEachNode
     */
    public function __construct($token) {
        parent::__construct($token);
        if (!preg_match(self::MATCH, $token->source, $matches)) {
            throw new SassEachNodeException('Invalid {what}', array('{what}' => '@each directive'), $this);
        }
        $this->variable = $matches[self::VARIABLE];
        $this->values = $matches[self::VALUES];
    }

    /**
     * Parse this node.
     * @param SassContext the context in which this node is parsed
     * @return array parsed child nodes
     */
    public function parse($context) {
        $children = array();
        $values = $this->evaluate($this->values, $context)->value;
        $values = explode(',', $values);
        
        $context = new SassContext($context);
        foreach ($values as $value) {
            $value = trim($value);
            $context->setVariable($this->variable, new SassString($value));
            $children = array_merge($children, $this->parseChildren($context));
        }
        return $children;
    }

}