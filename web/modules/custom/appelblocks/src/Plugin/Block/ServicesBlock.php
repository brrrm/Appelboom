<?php

namespace Drupal\appelblocks\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Entity\EntityTypeManager;

/**
 * Provides a 'services' block.
 *
 * @Block(
 *   id = "appelboom_services",
 *   admin_label = @Translation("Services by Appelboom"),
 *   category = @Translation("Appelboom")
 * )
 */

class ServicesBlock extends BlockBase implements ContainerFactoryPluginInterface{

	/**
	 * The entity type manager.
	 *
	 * @var \Drupal\Core\Entity\EntityTypeManagerInterface
	 */
	protected $entityTypeManager;


	/**
	* Constructs a Drupalist object.
	*
	* @param array $configuration
	*   A configuration array containing information about the plugin instance.
	* @param string $plugin_id
	*   The plugin_id for the plugin instance.
	* @param mixed $plugin_definition
	*   The plugin implementation definition.
	* @param \Drupal\Core\Session\AccountInterface $currentUser
	*   The current_user.
	*/
	public function __construct(array $configuration, $plugin_id, $plugin_definition, EntityTypeManager $entity_type_manager) {
		parent::__construct($configuration, $plugin_id, $plugin_definition);
		$this->entityTypeManager = $entity_type_manager;
	}

	/**
	* {@inheritdoc}
	*/
	public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
		return new static(
		  $configuration,
		  $plugin_id,
		  $plugin_definition,
		  $container->get('entity_type.manager')
		);
	}
	
	public function build(){
		$cache_tags = array();

		$services = [
			'#prefix'	=> '<aside class="header-services">',
			'#suffix'	=> '</aside>',
			'title'		=> [
				'#markup'	=> '<h2>Appelboom Consultancy levert experts in:</h2>'
			],
			'nodes'		=> []
		];
		$services_nids = \Drupal::entityQuery('taxonomy_term')
			->condition('vid','services')
			->sort('weight', 'ASC')
			->accessCheck(TRUE)
			->range(0,5)
			->execute();
		$services_nodes = $this->entityTypeManager->getStorage('taxonomy_term')->loadMultiple($services_nids);
		foreach ($services_nodes as $key => $service_node) {
			$services['nodes'][] = $this->entityTypeManager->getViewBuilder('taxonomy_term')->view($service_node, 'icon_link');
			$cache_tags = array_merge($cache_tags, $service_node->getCacheTags());
		}

		$return =  [
			'services'		=> $services,
			'#attributes'	=> [
				'class'		=> ['clearfix', 'block', 'servicesbyappelboom']
			],
			'#cache' => [
				'tags' => $cache_tags
			]
		];

		return $return;

	}
}
