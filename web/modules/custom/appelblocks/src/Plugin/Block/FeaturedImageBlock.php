<?php

namespace Drupal\appelblocks\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\file\Entity\File;

/**
 * Provides a 'featured image' block.
 *
 * @Block(
 *   id = "appelboom_fetured_image",
 *   admin_label = @Translation("Featured image for Appelboom"),
 *   category = @Translation("Appelboom")
 * )
 */

class FeaturedImageBlock extends BlockBase{
	
	public function build(){
		$cache_tags = array();

		// get current node
		$node = \Drupal::routeMatch()->getParameter('node');
		if($node){
			$cache_tags = $node->getCacheTags();
		}

		$title = NULL;

		if(!$node){
			return false;
		}
		
		if($node->hasField('field_featured_image') && !$node->get('field_featured_image')->isEmpty()){
			$media = $node->get('field_featured_image')->entity;
			$image = \Drupal::entityTypeManager()->getViewBuilder('media')->view($media, 'header');
			$request = \Drupal::request();
			$route_match = \Drupal::routeMatch();
			$title = \Drupal::service('title_resolver')->getTitle($request, $route_match->getRouteObject());
			$title = [
				'#prefix'	=> '<div class="title-subtitle">',
				'#markup'	=> sprintf('<h1 id="header-page-title" class="page__title title">%s</h1>', $title),
				'#suffix'	=> '</div>',
			];
		}

		if($node->hasField('field_subtitle') && !$node->get('field_subtitle')->isEmpty()){
			$display_options = [
				'label' => 'hidden',
				'type' => 'string',
			];
			$subtitle = $node->get('field_subtitle')->value;
			$title['#suffix'] = sprintf('<p class="subtitle"><em>%s</em></p></div>', $subtitle);
		}

		if($node->hasField('field_services') && !$node->get('field_services')->isEmpty()){
			$display_options = [
				'label' => 'above',
				'type' => 'entity_reference_entity_view',
				'settings' => [
					'view_mode' => 'icon_link',
				],
			];
			$services = $node->get('field_services')->view($display_options);
		}

		if($node->hasField('field_lead_consultant') && !$node->get('field_lead_consultant')->isEmpty()){
			$display_options = [
				'label' => 'above',
				'type' => 'entity_reference_entity_view',
				'settings' => [
					'view_mode' => 'icon_link',
				],
			];
			$consultant = $node->get('field_lead_consultant')->view($display_options);
		}

		if($node->hasField('field_case_study__client_name') && !$node->get('field_case_study__client_name')->isEmpty()){
			$display_options = [
				'label' => 'above',
				'type' => 'string',
			];
			$client = $node->get('field_case_study__client_name')->view($display_options);
		}

		$return =  [
			'main'			=> [
				'#prefix'		=> '<div class="main">',
				'#suffix'		=> '</div>',
				'title'			=> $title,
				'image'			=> $image ?? null,
			],
			'#attributes'	=>	[
				'class'			=> ['node-type-'.$node->bundle()]
			]
		];
		if(isset($client) || isset($services) || isset($consultant)){
			$return['sidebar'] = [
				'#prefix'		=> '<div class="sidebar">',
				'client'		=> $client ?? NULL,
				'services'		=> $services ?? NULL,
				'consultant'	=> $consultant ?? NULL,
				'#suffix'		=> '</div>',
			];
		}

		return $return;

	}
}
