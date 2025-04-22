<?php

namespace Drupal\appelblocks\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Url;
use Drupal\Core\Link;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Entity\EntityTypeManager;

/**
 * Provides a 'featured image' block.
 *
 * @Block(
 *   id = "appelboom_homepage_header",
 *   admin_label = @Translation("headerblock for homepage for Appelboom"),
 *   category = @Translation("Appelboom")
 * )
 */

class HomepageHeaderBlock extends BlockBase implements ContainerFactoryPluginInterface{

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

		// get current node
		$node = \Drupal::routeMatch()->getParameter('node');
		if(!$node){
			return false;
		}

		$cache_tags = $node->getCacheTags();
		$title = NULL;
		
		if($node->hasField('field_featured_image') && !$node->get('field_featured_image')->isEmpty()){
			$image = $node->get('field_featured_image')->view([
				'label'		=> 'hidden',
				'type'		=> 'media_thumbnail',
				'settings'	=> [
					'image_style'	=> '16_9_1800x1080_focal_point_webp'
				]
			]);
		}

		$request = \Drupal::request();
		$route_match = \Drupal::routeMatch();
		$title = \Drupal::service('title_resolver')->getTitle($request, $route_match->getRouteObject());
		$subtitle = $node->get('field_subtitle')->value ?? '';
		$title = [
			'#prefix'	=> '<div class="title-subtitle">',
			'title'		=> [
				'#markup'	=> sprintf('<h1 id="header-page-title" class="page__title title">%s <span class="inner-subtitle">%s</span></h1>', $title, $subtitle),
			],
			'#suffix'	=> '</div>',
		];


		$config = $this->getConfiguration();
		$link_node_id = $config['node_id'] ?? NULL;
		$url = $config['link'] ?? NULL;
		if($link_node_id){
			$title['link'] = Link::createFromRoute('Lees hoe wij dat doen', 'entity.node.canonical', ['node' => $link_node_id])->toRenderable();
			$title['link']['#prefix'] = '<p class="homepage-link"><i class="arrow" />';
			$title['link']['#suffix'] = '</p>';
		}

		if($link = $config['link'] ?? FALSE){
			$title['link']['#markup'] = '<a href="'.$link.'">Lees hoe wij dat doen</a>';
			$title['link']['#prefix'] = '<p class="homepage-link"><i class="arrow" />';
			$title['link']['#suffix'] = '</p>';
		}

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
		}

		if($node->hasField('field_description') && !$node->get('field_description')->isEmpty()){

			$display_options = [
				'label' => 'hidden',
				'type' => 'string',
			];
			$intro = $node->get('field_description')->view($display_options);
		}

		$return =  [
			'header'		=> [
				'#prefix'	=> '<div class="homepage-header-image">',
				'#suffix'	=> '</div>',
				'image'		=> $image ?? NULL,
				'title'		=> $title,
			],
			'services'		=> $services,
			'intro'			=> $intro ?? NULL,
			'#cache' => [
				'tags' => $cache_tags,
				'contexts' => ['url.path']
			]
		];

		return $return;
	}

	/**
	* {@inheritdoc}
	*/
	public function blockForm($form, FormStateInterface $form_state) {
		$form = parent::blockForm($form, $form_state);

		$form['node_id'] = [
			'#type' => 'entity_autocomplete',
			'#title' => 'Naar welke pagina wil je linken?',
			'#target_type' => 'node',
			'#selection_settings' => [
				'target_bundles' => ['person', 'page', 'case_study'],
			],
			'#default_value' => !empty($this->configuration['node_id']) ? $this->entityTypeManager->getStorage('node')->load($this->configuration['node_id']) : NULL,
			'#selection_handler' => 'default',
			'#description' => $this->t('Select a media item to display in this block.'),
		];

		$form['link'] = [
			'#type'		=> 'url',
			'#title'	=> 'link',
			//'#pattern'	=> '*.appelboomconsultancy.nl',
		];

		return $form;
	}

	/**
	* {@inheritdoc}
	*/
	public function blockSubmit($form, FormStateInterface $form_state) {
		$values = $form_state->getValues();

		$this->configuration['node_id'] = $values['node_id'];
		$this->configuration['link'] = $values['link'];
	}
}
