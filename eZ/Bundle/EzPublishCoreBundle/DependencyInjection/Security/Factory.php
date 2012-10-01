<?php
/**
 * File containing the Security Factory class.
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Security;

use Symfony\Bundle\SecurityBundle\DependencyInjection\Security\Factory\AbstractFactory,
    Symfony\Component\DependencyInjection\ContainerBuilder,
    Symfony\Component\DependencyInjection\Reference,
    Symfony\Component\DependencyInjection\DefinitionDecorator,
    Symfony\Component\Config\Definition\Builder\NodeDefinition;

class Factory extends AbstractFactory
{
    const AUTHENTICATION_PROVIDER_ID = 'ezpublish.security.auth';
    const AUTHENTICATION_LISTENER_ID = 'ezpublish_legacy.security.firewall_listener';
    const AUTHENTICATION_ENTRY_POINT_ID = 'ezpublish_legacy.security.auth.entry_point';

    /**
     * Subclasses must return the id of a service which implements the
     * AuthenticationProviderInterface.
     *
     * @param ContainerBuilder $container
     * @param string           $id             The unique id of the firewall
     * @param array            $config         The options array for this listener
     * @param string           $userProviderId The id of the user provider
     *
     * @return string never null, the id of the authentication provider
     */
    protected function createAuthProvider( ContainerBuilder $container, $id, $config, $userProviderId )
    {
        $providerId = self::AUTHENTICATION_PROVIDER_ID . ".$id";
        $container
            ->setDefinition( $providerId, new DefinitionDecorator( self::AUTHENTICATION_PROVIDER_ID ) )
            ->replaceArgument( 0, new Reference( $userProviderId ) )
        ;

        return $providerId;
    }

    protected function createEntryPoint( $container, $id, $config, $defaultEntryPointId )
    {
        $entryPointId = self::AUTHENTICATION_ENTRY_POINT_ID . ".$id";
        $container->setDefinition( $entryPointId, new DefinitionDecorator( self::AUTHENTICATION_ENTRY_POINT_ID ) );

        return $entryPointId;
    }

    /**
     * Subclasses must return the id of the listener template.
     *
     * Listener definitions should inherit from the AbstractAuthenticationListener
     * like this:
     *
     *    <service id="my.listener.id"
     *             class="My\Concrete\Classname"
     *             parent="security.authentication.listener.abstract"
     *             abstract="true" />
     *
     * In the above case, this method would return "my.listener.id".
     *
     * @return string
     */
    protected function getListenerId()
    {
        return self::AUTHENTICATION_LISTENER_ID;
    }

    public function getPosition()
    {
        return 'pre_auth';
    }

    public function getKey()
    {
        return 'ezpublish';
    }
}