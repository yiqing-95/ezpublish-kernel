<?php
/**
 * File containing the SuggestionCollectorAwareInterface class.
 *
 * @copyright Copyright (C) 1999-2014 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\Suggestion\Collector;

interface SuggestionCollectorAwareInterface
{
    /**
     * Injects SuggestionCollector.
     *
     * @param SuggestionCollectorInterface $suggestionCollector
     */
    public function setSuggestionCollector( SuggestionCollectorInterface $suggestionCollector );
}