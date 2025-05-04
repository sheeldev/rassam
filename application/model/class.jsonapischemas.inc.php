<?php

class RestApiSchemas
{
	public static $schemas = [
		'system_list_endpoints' => [
			'type' => 'object',
			'description' => 'List all available API endpoints with details.',
			'example_request' => [
				'method' => 'GET',
				'url' => '/api/system/endpoints/',
				'headers' => [
					'Authorization' => 'Bearer YOUR_ACCESS_TOKEN',
					'Accept' => 'application/json'
				]
			],
			'example_response' => [
				'status' => 'success',
				'message' => 'List of all API endpoints',
				'data' => []
			]
		],
		'system_connect' => [
			'type' => 'object',
			'description' => 'Authenticate a user and return an access token.',
			'required' => ['grant_type', 'user_id', 'user_secret', 'apikey'],
			'properties' => [
				'grant_type' => [
					'type' => 'string',
					'enum' => ['client_credentials']
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
			],
			'example_request' => [
				'method' => 'POST',
				'url' => '/api/system/connect/',
				'body' => [
					'grant_type' => 'client_credentials',
					'user_id' => 'example_user',
					'user_secret' => 'example_secret',
					'apikey' => 'example_apikey'
				]
			],
			'example_response' => [
				'status' => 'success',
				'message' => 'Connected.',
				'data' => [
					'token' => 'example_access_token'
				]
			]
		],
		'system_getofficialtime' => [
			'type' => 'object',
			'description' => 'Retrieve the current system time.',
			'required' => ['system'],
			'properties' => [
				'system' => [
					'type' => 'string',
					'enum' => ['time']
				]
			],
			'example_request' => [
				'method' => 'GET',
				'url' => '/api/system/officialtime/',
				'headers' => [
					'Authorization' => 'Bearer YOUR_ACCESS_TOKEN',
					'Accept' => 'application/json'
				]
			],
			'example_response' => [
				'status' => 'success',
				'message' => 'System time retrieved successfully',
				'data' => [
					'datetime' => '2025-05-04 12:00:00'
				]
			]
		],
		'core_scan_post' => [
			'type' => 'object',
			'description' => 'Insert new scan activity records.',
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
							'sales_line_unit_id' => [
								'type' => 'string',
								'minLength' => 15,
								'maxLength' => 48
							],
							'sales_line_id' => [
								'type' => 'string',
								'minLength' => 15,
								'maxLength' => 48
							],
							'assembly_no' => [
								'type' => 'string',
								'minLength' => 5,
								'maxLength' => 20
							],
							'mo_no' => [
								'type' => 'string',
								'minLength' => 5,
								'maxLength' => 50
							],
							'item_code' => [
								'type' => 'string',
								'minLength' => 4,
								'maxLength' => 20
							],
							'design_code' => [
								'type' => 'string',
								'minLength' => 4,
								'maxLength' => 50
							],
							'variant_code' => [
								'type' => 'string',
								'minLength' => 4,
								'maxLength' => 10
							],
							'so_no' => [
								'type' => 'string',
								'minLength' => 4,
								'maxLength' => 20
							],
							'activity_code' => [
								'type' => 'string',
								'minLength' => 3,
								'maxLength' => 10
							],
							'activity_name' => [
								'type' => 'string',
								'minLength' => 1,
								'maxLength' => 100
							],
							'activity_remark' => [
								'type' => 'string',
								'nullable' => true,
								'minLength' => 0,
								'maxLength' => 255
							],
							'activity_type' => [
								'type' => 'string',
								'enum' => ['In', 'Out']
							],
							'activity_date' => [
								'type' => 'string',
								'pattern' => '^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$',
								'errorMessage' => 'The activity_date must be in the format YYYY-MM-DD HH:MM:SS'
							],
							'activity_time' => [
								'type' => 'integer',
								'minimum' => 0
							]
						]
					]
				]
			],
			'example_request' => [
				'method' => 'POST',
				'url' => '/api/core/scan/',
				'headers' => [
					'Authorization' => 'Bearer YOUR_ACCESS_TOKEN',
					'Accept' => 'application/json'
				],
				'body' => [
					'data' => [
						[
							'sales_line_unit_id' => 'Data',
							'sales_line_id' => 'Data',
							'assembly_no' => 'Data',
							'mo_no' => 'Data',
							'item_code' => 'Data',
							'design_code' => 'Data',
							'variant_code' => 'Data',
							'so_no' => 'Data',
							'activity_code' => 'Data',
							'activity_name' => 'Data',
							'activity_remark' => 'Data',
							'activity_type' => 'In/Out',
							'activity_date' => 'YYYY-MM-DD HH:MM:SS',
							'activity_time' => 'Time in seconds (Unix timestamp)'
						]
					]
				]
			],
			'example_response' => [
				'status' => 'success',
				'message' => 'All records inserted successfully',
				'data' => []
			]
		],
		'core_scan_get' => [
			'type' => 'object',
			'description' => 'Retrieve scan activities based on filters.',
			'required' => ['filter'],
			'properties' => [
				'filter' => ['type' => 'string'],
				'orderby' => ['type' => 'string']
			],
			'example_request' => [
				'method' => 'GET',
				'url' => '/api/core/scan/?filter=sales_line_id eq \'123\'&orderby=activity_date desc',
				'headers' => [
					'Authorization' => 'Bearer YOUR_ACCESS_TOKEN',
					'Accept' => 'application/json'
				]
			],
			'example_response' => [
				'status' => 'success',
				'message' => 'Data retrieved successfully',
				'data' => [
					[
						'sales_line_unit_id' => 'Data',
						'sales_line_id' => 'Data',
						'assembly_no' => 'Data',
						'mo_no' => 'Data',
						'item_code' => 'Data',
						'design_code' => 'Data',
						'variant_code' => 'Data',
						'so_no' => 'Data',
						'activity_code' => 'Data',
						'activity_name' => 'Data',
						'activity_remark' => 'Data',
						'activity_type' => 'In/Out',
						'activity_date' => 'YYYY-MM-DD HH:MM:SS',
						'activity_time' => 'Time in seconds (Unix timestamp)'
					]
				]
			]
		]
	];
}