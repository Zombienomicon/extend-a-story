<?php

/*

Extend-A-Story - Interactive, Extendable, Choose Your Own Adventure Story
Copyright (C) 2002-2016 Jeffrey J. Weston


This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA


For information about Extend-A-Story and its authors, please visit the website:
http://www.sir-toby.com/extend-a-story/

*/

namespace Extend_A_Story\HtmlElements;

class InputField extends HtmlElement
{
    private $name;
    private $label;
    private $type;
    private $value;
    private $helpText;

    public function __construct( $name, $label, $type, $value, $helpText )
    {
        $this->name     = $name;
        $this->label    = $label;
        $this->type     = $type;
        $this->value    = $value;
        $this->helpText = $helpText;
    }

    public function render()
    {

?>

<div class="inputField">
    <div>
        <label for="<?php echo( htmlentities( $this->name )); ?>">
            <?php echo( htmlentities( $this->label )); ?>:
        </label>
    </div>
    <div>
        <span onclick="toggleVisibility( '<?php echo( htmlentities( $this->name )); ?>-help' );"
              class="inputFieldHelpButton">Help</span>
    </div>
    <div id="<?php echo( htmlentities( $this->name )); ?>-help"
         class="inputFieldHelpContents" style="display: none;">
        <?php echo( htmlentities( $this->helpText )); ?>
    </div>
    <input type="<?php echo( htmlentities( $this->type )); ?>"
           id="<?php echo( htmlentities( $this->name )); ?>"
           name="<?php echo( htmlentities( $this->name )); ?>"
           value="<?php echo( htmlentities( $this->value )); ?>" />
</div>

<?php

    }
}

?>
