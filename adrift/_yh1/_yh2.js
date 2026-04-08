{

	"metadata": {
		"formatVersion": 0.821,
		"type": "ion_scene"
	},

	"urlBaseType": "",

	"objects": {

		"main_group": {
			"position": [0, 0, 0],
			"rotation": [0, 0, 0],
			"scale": [1, 1, 1],
			"visible": true,
			"children": {

				"prod_group": {
					"position": [0, -20, 0],
					"rotation": [-1.57, 0, 0],
					"scale": [1, 1, 1],
					"visible": true,
					"children": {

						"house": {
							"geometry": "yh1",
							"material": "mm_procedural",
							"position": [0, 0, 0],
							"rotation": [0, 0, 0],
							"scale": [1, 1, 1],
							"visible": true,
							"children": {}
						},
						
						"dome": {
							"geometry": "dome",
							"material": "m_dome1",
							"position": [0, 0, 0],
							"rotation": [0, 0, -1.57],
							"scale": [1.3, 1.3, 1.3],
							"visible": false,
							"children": {}
						},
						
						"ground": {
							"geometry": "ground",
							"material": "m_water",
							"position": [0, 0,-2],
							"rotation": [0, 0, 0],
							"scale": [1.3, 1.3, 0.3],
							"visible": false,
							"children": {}
						}
					}
				},
				
				

				"dummy": {
					"geometry": "geo_dummy",
					"material": "mat_none",
					"position": [0, -100, 0],
					"rotation": [-1.57, 0, 0],
					"scale": [1, 1, 1],
					"visible": false,
					"userData": {}
				},

				"Default": {
					"type": "PerspectiveCamera",
					"fov": 45,
					"aspect": 1.33333,
					"near": 1,
					"far": 1000,
					"position": [80, 0, -200],
					"target": [0, 0, 0],
					"userData": {
						"easing": "easeInOutCubic",
						"parameters": {
							"minDistance": 0,
							"maxDistance": 290,
							"minPolarAngle": 0,
							"maxPolarAngle": 1.57,
							"enableDamping": true,
							"dampingFactor": 0.1,
							"rotateSpeed": 0.15,
							"autoRotateSpeed": 0.1,
							"speed": 0.1,
							"zoomSpeed": 1.0,
							"panSpeed": 0.20,
							"enablePan ": false
						}
					},
					"children": {

						"light_cam": {
							"type": "PointLight",
							"position": [0, 22, 10],
							"color": 16579831,
							"intensity": 0.3
						},

						"light_left": {
							"type": "PointLight",
							"position": [-66, 12, -60],
							"color": 12635388,
							"intensity": 0.15
						},

						"lightl_right_low": {
							"type": "PointLight",
							"position": [120, -8, -40],
							"color": 15267260,
							"intensity": 0.15
						}
					}
				},

				"Front": {
					"type": "PerspectiveCamera",
					"fov": 35,
					"aspect": 1.33333,
					"near": 1,
					"far": 1000,
					"position": [0, 15, 200],
					"target": [0, 0, 0],
					"userData": {
						"easing": "easeInOutCubic"
					}
				},

				"Left": {
					"type": "PerspectiveCamera",
					"fov": 40,
					"aspect": 1.33333,
					"near": 1,
					"far": 1000,
					"position": [-200, 20, 0],
					"target": [0, 0, 0],
					"userData": {
						"easing": "easeInOutCubic"
					}
				},

				"Left_0": {
					"type": "PerspectiveCamera",
					"fov": 40,
					"aspect": 1.33333,
					"near": 1,
					"far": 1000,
					"position": [-200, 0, 0],
					"target": [0, 0, 0],
					"userData": {
						"easing": "easeInOutCubic"
					}
				},

				"Right": {
					"type": "PerspectiveCamera",
					"fov": 40,
					"aspect": 1.33333,
					"near": 1,
					"far": 1000,
					"position": [200, 20, 0],
					"target": [0, 0, 0],
					"userData": {
						"easing": "easeInOutCubic"
					}
				},

				"Back": {
					"type": "PerspectiveCamera",
					"fov": 40,
					"aspect": 1.33333,
					"near": 1,
					"far": 1000,
					"position": [0, 20, -200],
					"target": [0, 0, 0],
					"userData": {
						"easing": "easeInOutCubic"
					}
				},

				"Top": {
					"type": "PerspectiveCamera",
					"fov": 40,
					"aspect": 1.33333,
					"near": 1,
					"far": 1000,
					"position": [0, 200, -1],
					"target": [0, 0, 0],
					"userData": {
						"easing": "easeInOutCubic"
					}
				}
			}
		}
	},

	"geometries": {

		"plane": {
			"type": "plane",
			"width": 5,
			"height": 5,
			"widthSegments": 10,
			"heightSegments": 10
		},

		"geo_dummy": {
			"type": "plane",
			"width": 0.0001,
			"height": 0.0001,
			"widthSegments": 1,
			"heightSegments": 1
		},

		"sprite_geo": {
			"type": "plane",
			"width": 6,
			"height": 6,
			"widthSegments": 1,
			"heightSegments": 1
		},

		"yh1": {
			"type": "ion",
			"url": "_3d/yh1.ion"
		},

		"dome": {
			"type": "ion",
			"url": "_3d/yh1_dome.ion"
		},

		"ground": {
			"type": "ion",
			"url": "_3d/yh1_ground.ion"
		}
	},

	"embeds": {},

	"materials": {

		"basic_gray": {
			"type": "MeshBasicMaterial",
			"parameters": {
				"color": 2236962,
				"wireframe": true,
				"opacity": 0.15,
				"transparent": true,
				"blending": "AdditiveBlending"
			}
		},

		"orange_wire": {
			"type": "MeshBasicMaterial",
			"parameters": {
				"color": 13500241,
				"wireframe": false,
				"opacity": 1.0,
				"transparent": true,
				"depthTest": true,
				"blending": "NormalBlending"

			}
		},

		"red_wire": {
			"type": "MeshBasicMaterial",
			"parameters": {
				"color": 16715776,
				"wireframe": true,
				"opacity": 0.08,
				"transparent": true,
				"depthTest": true,
				"blending": "NormalBlending"

			}
		},

		"blue_wire": {
			"type": "MeshBasicMaterial",
			"parameters": {
				"color": 42495,
				"wireframe": true,
				"opacity": 0.08,
				"transparent": true,
				"depthTest": true,
				"blending": "NormalBlending"

			}
		},

		"blue2_wire": {
			"type": "MeshBasicMaterial",
			"parameters": {
				"color": 3014911,
				"wireframe": true,
				"opacity": 0.08,
				"transparent": true,
				"depthTest": true,
				"blending": "NormalBlending"

			}
		},

		"m_dome1": {
			"type": "MeshBasicMaterial",
			"parameters": {
				"color": 16777215,
				"map": "bg_dome1",
				"blending": "NormalBlending"

			}
		},

		"m_01": {
			"type": "MeshPhysicalMaterial",
			"parameters": {
				"map": "tex_yh1",
				"roughnessMap": "tex_yh1_r",
				"roughness": 0.65,
				"metalness": 0.0,
				"envMapIntensity": 2.0,
				"emissiveIntensity": 0.0,
				"bumpScale": 0.2,
				"color": 16777215,
				"emissive": 16777215,
				"envMap": "env_dome1",
				"side": "double"
			}
		},
		
		"m_02": {
			"type": "MeshPhysicalMaterial",
			"parameters": {
				"map": "tex_wood",
				"bumpMap": "tex_wood_r",
				"roughnessMap": "tex_wood_r",
				"roughness": 0.55,
				"metalness": 0.1,
				"envMapIntensity": 1.0,
				"emissiveIntensity": 0.0,
				"bumpScale": -0.02,
				"color": 15658734,
				"emissive": 15658734,
				"envMap": "env_dome1",
				"side": "double"
			}
		},
		
		"m_03": {
			"type": "MeshPhysicalMaterial",
			"parameters": {
				"map": "tex_metal",
				"bumpMap": "tex_metal",
				"roughnessMap": "tex_metal",
				"roughness": 2.5,
				"metalness": 0.3,
				"envMapIntensity": 3.0,
				"emissiveIntensity": 0.0,
				"bumpScale": 0.04,
				"color": 6710886,
				"emissive": 6710886,
				"envMap": "env_dome1",
				"side": "double"
			}
		},
		
		"m_water": {
			"type": "MeshPhysicalMaterial",
			"parameters": {
				"bumpMap": "tex_water",
				"roughness": 0.15,
				"metalness": 0.1,
				"envMapIntensity": 1.1,
				"emissiveIntensity": 0.0,
				"bumpScale": 0.7,
				"color": 5592405,
				"emissive": 5592405,
				"envMap": "env_dome1",
				"side": "double"
			}
		},


		"mat_none": {
			"type": "MeshPhongMaterial",
			"parameters": {
				"color": 0,
				"opacity": 0.0,
				"transparent": true
			}
		},

		"mm_procedural": {
			"type": "MultiMaterial",
			"parameters": {
				"materials": [
					"m_01",
					"m_02",
					"m_03"

				]
			}
		}
	},

	"textures": {

		"env_dome1": {
			"url": "_tex/env/beach1.jpg",
			"magFilter": "LinearFilter",
			"minFilter": "LinearMipMapLinearFilter",
			"mapping": "EquirectangularReflectionMapping"
		},

		"bg_dome1": {
			"url": "_tex/env/beach1.jpg",
			"anisotropy": 8,
			"repeat": [1, 1],
			"magFilter": "LinearFilter",
			"minFilter": "LinearMipMapLinearFilter",
			"encoding": "LinearEncoding"
		},

		"tex_yh1": {
			"url": "_tex/yh1.jpg",
			"anisotropy": 8,
			"repeat": [1, 1],
			"magFilter": "LinearFilter",
			"minFilter": "LinearMipMapLinearFilter",
			"encoding": "LinearEncoding"
		},

		"tex_yh1_r": {
			"url": "_tex/yh1_r.jpg",
			"anisotropy": 8,
			"repeat": [1, 1],
			"magFilter": "LinearFilter",
			"minFilter": "LinearMipMapLinearFilter",
			"encoding": "LinearEncoding"
		},

		"tex_wood": {
			"url": "_tex/wood.jpg",
			"anisotropy": 8,
			"repeat": [2, 2],
			"magFilter": "LinearFilter",
			"minFilter": "LinearMipMapLinearFilter",
			"encoding": "LinearEncoding"
		},

		"tex_wood_r": {
			"url": "_tex/wood_r.jpg",
			"anisotropy": 8,
			"repeat": [2, 2],
			"magFilter": "LinearFilter",
			"minFilter": "LinearMipMapLinearFilter",
			"encoding": "LinearEncoding"
		},

		"tex_metal": {
			"url": "_tex/metal.jpg",
			"anisotropy": 8,
			"repeat": [1, 1],
			"magFilter": "LinearFilter",
			"minFilter": "LinearMipMapLinearFilter",
			"encoding": "LinearEncoding"
		},

		"tex_water": {
			"url": "_tex/waternormals.jpg",
			"anisotropy": 8,
			"repeat": [2, 2],
			"magFilter": "LinearFilter",
			"minFilter": "LinearMipMapLinearFilter",
			"wrap": "repeat"
		},

		"tex_uv": {
			"url": "tex/uv.jpg",
			"anisotropy": 8,
			"repeat": [2, 2],
			"magFilter": "LinearFilter",
			"minFilter": "LinearMipMapLinearFilter",
			"wrap": "repeat"
		}

	},

	"fogs": {},

	"defaults": {
		"bgcolor": [0, 0, 0],
		"bgalpha": 1,
		"camera": "my_camera"
	}

}
