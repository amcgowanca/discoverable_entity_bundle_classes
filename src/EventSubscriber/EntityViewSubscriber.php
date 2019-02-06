<?php

namespace Drupal\discoverable_entity_bundle_classes\EventSubscriber;

use Symfony\Component\HttpKernel\Controller\ArgumentResolverInterface;
use Drupal\Core\DependencyInjection\ClassResolverInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\EventSubscriber\EarlyRenderingControllerWrapperSubscriber;
use Drupal\Core\Routing\CurrentRouteMatch;
use Drupal\discoverable_entity_bundle_classes\ContentEntityBundleClassManagerInterface;
use Drupal\discoverable_entity_bundle_classes\ContentEntityBundleInterface;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Drupal\Core\Render\RendererInterface;

/**
 * Defines an early rendering controller wrapper, acting on controller events.
 *
 * @todo: Investigate and adapt implementation for Drupal 9.
 * Further review of this implementation will need to be considered in
 * the future for Drupal 9.0. It is understood that Early Rendering will not be
 * available in Drupal 9 and the core provided subscribere is deprecated.
 */
class EntityViewSubscriber extends EarlyRenderingControllerWrapperSubscriber {

  /**
   * The entity type manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The discoverable entity class manager.
   *
   * @var \Drupal\discoverable_entity_bundle_classes\ContentEntityBundleClassManagerInterface
   */
  protected $entityClassManager;

  /**
   * The current route match.
   *
   * @var \Drupal\Core\Routing\CurrentRouteMatch
   */
  protected $currentRouteMatch;

  /**
   * The class resolver service.
   *
   * @var \Drupal\Core\DependencyInjection\ClassResolverInterface
   */
  protected $classResolver;

  /**
   * EntityViewSubscriber constructor.
   *
   * @param \Symfony\Component\HttpKernel\Controller\ArgumentResolverInterface $argument_resolver
   *   The argument resolver service.
   * @param \Drupal\Core\Render\RendererInterface $renderer
   *   The renderer.
   * @param \Drupal\Core\DependencyInjection\ClassResolverInterface $class_resolver
   *   The class resolver.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager service.
   * @param \Drupal\discoverable_entity_bundle_classes\ContentEntityBundleClassManagerInterface $entity_class_manager
   *   The discoverable entity class manager service.
   * @param \Drupal\Core\Routing\CurrentRouteMatch $current_route_match
   *   The current route match.
   */
  public function __construct(ArgumentResolverInterface $argument_resolver, RendererInterface $renderer, ClassResolverInterface $class_resolver, EntityTypeManagerInterface $entity_type_manager, ContentEntityBundleClassManagerInterface $entity_class_manager, CurrentRouteMatch $current_route_match) {
    parent::__construct($argument_resolver, $renderer);
    $this->classResolver = $class_resolver;
    $this->entityTypeManager = $entity_type_manager;
    $this->entityClassManager = $entity_class_manager;
    $this->currentRouteMatch = $current_route_match;
  }

  /**
   * Acts and responds to controller negotiation events.
   *
   * @param \Symfony\Component\HttpKernel\Event\FilterControllerEvent $event
   *   The event.
   *
   * @throws \Exception
   *   Any non-caught exceptions are passed through.
   */
  public function onController(FilterControllerEvent $event) {
    if (!$event->isMasterRequest()) {
      return;
    }

    if (strpos($this->currentRouteMatch->getRouteName(), 'entity.') !== 0) {
      return;
    }

    list(, $entity_type_id, $route_type) = explode('.', $this->currentRouteMatch->getRouteName());

    /** @var \Drupal\Core\Entity\ContentEntityInterface $entity */
    $entity = $this->currentRouteMatch->getParameter($entity_type_id);
    if (!($entity instanceof ContentEntityBundleInterface)) {
      return;
    }

    /** @var \Drupal\discoverable_entity_bundle_classes\ContentEntityBundleClassPlugin $entity_class_plugin */
    $entity_class_plugin = $this->entityClassManager->getPlugin($entity->getEntityType(), $entity->bundle());
    if (!$entity_class_plugin) {
      return;
    }

    $handlers = $entity_class_plugin->getHandlers();
    if (!empty($handlers['controllers'][$route_type])) {
      $default_controller = $this->currentRouteMatch->getRouteObject()->getDefault('_controller');
      list($default_controller_class) = explode('::', $default_controller);
      $default_controller_reflection = new \ReflectionClass($default_controller_class);

      list($controller_class, $controller_method) = explode('::', $handlers['controllers'][$route_type]);
      $reflection = new \ReflectionClass($controller_class);
      if (!$reflection->isSubclassOf($default_controller_reflection->getName())) {
        throw new \InvalidArgumentException(sprintf('"%s" is not of type "%s".', $handlers['view_controller'], $default_controller_class));
      }

      $controller = [$this->classResolver->getInstanceFromDefinition($controller_class), $controller_method];
      $arguments = $this->argumentResolver->getArguments($event->getRequest(), $controller);

      $event->setController(function () use ($controller, $arguments) {
        return $this->wrapControllerExecutionInRenderContext($controller, $arguments);
      });

      $event->stopPropagation();
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    return [
      KernelEvents::CONTROLLER => ['onController'],
    ];
  }

}
