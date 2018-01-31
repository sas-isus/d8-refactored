<?php

namespace Drupal\gathercontent_upload\Event;

/**
 * Defines events for the GatherContent module.
 */
final class GatherUploadContentEvents {

  /**
   * Name of the event fired before we post node to GatherContent.
   *
   * This event allows modules to perform an action before node is uploaded
   * from Drupal to GatherContent. The event listener method receives
   * a \Drupal\gathercontent\Event\PreNodeUploadEvent instance.
   *
   * @Event
   *
   * @see \Drupal\gathercontent\Event\PreNodeUploadEvent
   *
   * @var string
   */
  const PRE_NODE_UPLOAD = 'gathercontent.pre_node_upload';

  /**
   * Name of the event fired after we post node to GatherContent.
   *
   * This event allows modules to perform an action after node is uploaded
   * to GatherContent from Drupal. The event is triggered only after successful
   * upload. The event listener method receives
   * a \Drupal\gathercontent\Event\PostNodeSaveEvent instance.
   *
   * @Event
   *
   * @see \Drupal\gathercontent\Event\PostNodeUploadEvent
   *
   * @var string
   */
  const POST_NODE_UPLOAD = 'gathercontent.post_node_upload';

  /**
   * Name of the event fired after we post node to GatherContent.
   *
   * This event allows modules to perform an action after selected nodes are
   * uploaded to GatherContent from Drupal. The event listener method receives
   * a \Drupal\gathercontent\Event\PostUploadEvent instance.
   *
   * @Event
   *
   * @see \Drupal\gathercontent\Event\PostUploadEvent
   *
   * @var string
   */
  const POST_UPLOAD = 'gathercontent.post_upload';

}
