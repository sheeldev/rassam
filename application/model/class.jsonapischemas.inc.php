<?php

class RestApiSchemas
{
	public static $schemas = [
		'list_endpoints' => [
			'type' => 'object'
		],
		'system_connect' => [
			'type' => 'object',
			'required' => ['grant_type', 'user_id', 'user_secret', 'apikey'],
			'optional' => [],
			'properties' => [
				'grant_type' => [
					'type' => 'string',
					'enum' => ['client_credentials'],
					'minLength' => 5,
					'maxLength' => 20
				],
				'user_id' => [
					'type' => 'string',
					'minLength' => 3,
					'maxLength' => 60
				],
				'user_secret' => [
					'type' => 'string',
					'minLength' => 8,
					'maxLength' => 100
				],
				'apikey' => [
					'type' => 'string',
					'minLength' => 32,
					'maxLength' => 64
				]
			]
		],
		'system_getofficialtime' => [
			'type' => 'object',
			'required' => ['system'],
			'properties' => [
				'system' => [
					'type' => 'string',
					'enum' => ['time'],
					'minLength' => 5,
					'maxLength' => 20
				]
			]
		],
		'core_scan_post' => [
			'type' => 'object',
			'required' => ['data'],
			'properties' => [
				'data' => [
					'type' => 'array',
					'items' => [
						'type' => 'object',
						'required' => [
							'sales_line_unit_id',
							'sales_line_id',
							'assembly_no',
							'mo_no',
							'item_code',
							'design_code',
							'variant_code',
							'so_no',
							'activity_code',
							'activity_name',
							'activity_type',
							'activity_date',
							'activity_time'
						],
						'properties' => [
							'sales_line_unit_id' => ['type' => 'string'],
							'sales_line_id' => ['type' => 'string'],
							'assembly_no' => ['type' => 'string'],
							'mo_no' => ['type' => 'string'],
							'item_code' => ['type' => 'string'],
							'design_code' => ['type' => 'string'],
							'variant_code' => ['type' => 'string'],
							'so_no' => ['type' => 'string'],
							'activity_code' => ['type' => 'string'],
							'activity_name' => ['type' => 'string'],
							'activity_remark' => ['type' => 'string', 'nullable' => true],
							'activity_type' => ['type' => 'string', 'enum' => ['In', 'Out']],
							'activity_date' => [
								'type' => 'string',
								'pattern' => '^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$', // Regex for YYYY-MM-DD HH:MM:SS
								'errorMessage' => 'The activity_date must be in the format YYYY-MM-DD HH:MM:SS'
							],
							'activity_time' => ['type' => 'integer']
						]
					]
				]
			]
		],
		'core_scan_get' => [
			'required' => ['filter'],
			'optional' => ['orderby'],
			'properties' => [
				'filter' => ['type' => 'string'], // Example: "sales_line_unit_id eq '123'"
				'orderby' => ['type' => 'string'] // Example: "sales_line_unit_id asc"
			]
		]
	];
}