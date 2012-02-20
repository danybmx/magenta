<?php
/**
 * Magenta, PHP Lightweight and easy to use MVC Framework
 *
 * @version 0.1
 * @package Template
 * @author dpStudios Development Team
 * @copyright dpStudios 2009-2011
 * @link http://magenta.dpstudios.es
 */

/**
 * Interface for template renderers
 */
interface Template_RendererInterface
{
	public function __construct($content);
	public function parse();
}