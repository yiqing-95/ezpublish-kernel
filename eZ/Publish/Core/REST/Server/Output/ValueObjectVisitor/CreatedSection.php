<?php
/**
 * File containing the CreatedSection ValueObjectVisitor class
 *
 * @copyright Copyright (C) 1999-2014 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\REST\Server\Output\ValueObjectVisitor;

use eZ\Publish\Core\REST\Common\Output\Generator;
use eZ\Publish\Core\REST\Common\Output\Visitor;

/**
 * CreatedSection value object visitor
 * @todo coverage add unit test
 */
class CreatedSection extends Section
{
    /**
     * Visit struct returned by controllers
     *
     * @param \eZ\Publish\Core\REST\Common\Output\Visitor $visitor
     * @param \eZ\Publish\Core\REST\Common\Output\Generator $generator
     * @param \eZ\Publish\Core\REST\Server\Values\CreatedSection $data
     */
    public function visit( Visitor $visitor, Generator $generator, $data )
    {
        parent::visit( $visitor, $generator, $data->section );
        $visitor->setHeader(
            'Location',
            $this->router->generate(
                'ezpublish_rest_loadSection',
                array( 'sectionId' => $data->section->id )
            )
        );
        $visitor->setStatus( 201 );
    }
}
