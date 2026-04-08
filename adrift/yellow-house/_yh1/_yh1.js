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

				"house_group": {
					"position": [0, -20, 0],
					"rotation": [-1.57, 0, 0],
					"scale": [1, 1, 1],
					"visible": true,
					"children": {

						"h1_group": {
							"position": [0, 0, -0.8],
							"rotation": [0, 0, 0],
							"scale": [1, 1, 1],
							"visible": true,
							"children": {

								"h2_group": {
									"position": [0, 0, 0],
									"rotation": [0, 0, 0],
									"scale": [1, 1, 1],
									"visible": true,
									"children": {

										"h3_group": {
											"position": [0, 0, 0],
											"rotation": [0, 0, 0],
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
													"children": {},
													"userData" : {
														"animations" : {
														}
													}
												}
											}
										}
									}
								}
							}
						},

						"ground": {
							"geometry": "plane",
							"material": "m_water",
							"position": [0, 0, -14],
							"rotation": [0, 0, 0],
							"scale": [1, 1, 1],
							"visible": true,
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
					"fov": 55,
					"aspect": 1.33333,
					"near": 1,
					"far": 5000,
					"position": [-88, 0, 260],
					"target": [0, 0, 0],
					"userData": {
						"easing": "easeInOutCubic",
						"parameters": {
							"minDistance": 0,
							"maxDistance": 400,
							"minPolarAngle": 0,
							"maxPolarAngle": 1.57,
							"enableDamping": true,
							"dampingFactor": 0.1,
							"rotateSpeed": 0.15,
							"autoRotateSpeed": 0.1,
							"speed": 0.1,
							"zoomSpeed": 1.0,
							"panSpeed": 0.20,
							"enablePan ": false,
							"enableRotate": false,
							"enableZoom": false,
							"screenSpacePanning": true

						}
					},
					"children": {

						"cam_mask": {
							"geometry": "cam_helper",
							"material": "m_cam",
							"position": [0, 0, 0],
							"rotation": [0, 0, 0],
							"scale": [1, 1, 1],
							"visible": true,
							"children": {}
						},

						"light_cam": {
							"type": "PointLight",
							"position": [0, 15, 20],
							"color": 15204261,
							"intensity": 0.2
						},

						"light_ambient": {
							"type": "AmbientLight",
							"color": 4738132,
							"intensity": 0.1
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
				},

				"Morning_CAM": {
					"type": "PerspectiveCamera",
					"fov": 55,
					"aspect": 1.33333,
					"near": 1,
					"far": 1000,
					"position": [-85.59,0,54.55],
					"target": [8.49, 0, 40.69],
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
			"width": 50000,
			"height": 50000,
			"widthSegments": 1,
			"heightSegments": 1
		},

		"geo_dummy": {
			"type": "plane",
			"width": 0.0001,
			"height": 0.0001,
			"widthSegments": 1,
			"heightSegments": 1
		},

		"cam_helper": {
			"type": "cube",
			"width": 20,
			"height": 20,
			"depth": 20,
			"widthSegments": 1,
			"heightSegments": 1,
			"depthSegments": 1
		},

		"yh1": {
			"type": "ion",
			"url": "_3d/yh1_2.ion"
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

		"m_cam": {
			"type": "MeshBasicMaterial",
			"parameters": {
				"color": 2703710,
				"opacity": 1.0,
				"transparent": true,
				"blending": "NormalBlending"

			}
		},

		"m_01": {
			"type": "MeshPhysicalMaterial",
			"parameters": {
				"map": "tex_yh1",
				"roughnessMap": "tex_yh1",
				"bumpMap": "tex_yh1",
				"roughness": 1.7,
				"metalness": 0.0,
				"envMapIntensity": 4.0,
				"emissiveIntensity": 0.0,
				"bumpScale": 0.1,
				"color": 16777215,
				"emissive": 16777215,
				"envMap": "env_cube1",
				"side": "double"
			}
		},

		"m_04": {
			"type": "MeshPhysicalMaterial",
			"parameters": {
				"map": "tex_yh2",
				"roughnessMap": "tex_yh2",
				"bumpMap": "tex_yh2",
				"roughness": 1.7,
				"metalness": 0.0,
				"envMapIntensity": 4.0,
				"emissiveIntensity": 0.0,
				"bumpScale": 0.1,
				"color": 16777215,
				"emissive": 16777215,
				"envMap": "env_cube1",
				"side": "double"
			}
		},

		"m_05": {
			"type": "MeshPhysicalMaterial",
			"parameters": {
				"map": "tex_yh3",
				"roughnessMap": "tex_yh3",
				"bumpMap": "tex_yh3",
				"roughness": 0.85,
				"metalness": 0.0,
				"envMapIntensity": 3.0,
				"emissiveIntensity": 0.0,
				"bumpScale": 0.3,
				"color": 16777215,
				"emissive": 16777215,
				"envMap": "env_cube1",
				"side": "double"
			}
		},

		"m_02": {
			"type": "MeshPhysicalMaterial",
			"parameters": {
				"map": "tex_wood",
				"bumpMap": "tex_wood_r",
				"roughnessMap": "tex_wood_r",
				"roughness": 3.1,
				"metalness": 0.1,
				"envMapIntensity": 1.0,
				"emissiveIntensity": 0.0,
				"bumpScale": -0.15,
				"color": 13421772,
				"emissive": 13421772,
				"envMap": "env_cube1",
				"side": "double"
			}
		},

		"m_03": {
			"type": "MeshPhysicalMaterial",
			"parameters": {
				"map": "tex_metal",
				"bumpMap": "tex_metal",
				"roughnessMap": "tex_metal",
				"roughness": 0.45,
				"metalness": 0.3,
				"envMapIntensity": 4.0,
				"emissiveIntensity": 0.0,
				"bumpScale": 0.06,
				"color": 6710886,
				"emissive": 6710886,
				"envMap": "env_cube1",
				"side": "double"
			}
		},

		"m_water": {
			"type": "MeshPhysicalMaterial",
			"parameters": {
				"roughness": 0.8,
				"metalness": 0.2,
				"envMapIntensity": 0.0,
				"emissiveIntensity": 0.0,
				"bumpScale": 0.0,
				"color": 2371657,
				"emissive": 2371657,
				"envMap": "env_cube1",
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
					"m_03",
					"m_04",
					"m_05"

				]
			}
		}
	},

	"textures": {

		"env_cube2": {
			"url": "_tex/env/sea1.jpg",
			"magFilter": "LinearFilter",
			"minFilter": "LinearMipMapLinearFilter",
			"mapping": "EquirectangularReflectionMapping"
		},

		"env_cube1": {
			"url": [
				"_tex/env/q01_sides.jpg",
				"_tex/env/q01_sides.jpg",
				"_tex/env/q01_top.jpg",
				"_tex/env/q01_bottom.jpg",
				"_tex/env/q01_sides.jpg",
				"_tex/env/q01_sides.jpg"
			]
		},

		"bg_dome1": {
			"url": "_tex/env/sea1.jpg",
			"anisotropy": 8,
			"repeat": [1, 1],
			"magFilter": "LinearFilter",
			"minFilter": "LinearMipMapLinearFilter",
			"encoding": "LinearEncoding"
		},

		"tex_yh1": {
			"url": "_tex/yh_02.jpg",
			"anisotropy": 16,
			"repeat": [1, 1],
			"magFilter": "LinearFilter",
			"minFilter": "LinearMipMapLinearFilter",
			"encoding": "LinearEncoding"
		},

		"tex_yh2": {
			"url": "_tex/yh_01.jpg",
			"anisotropy": 16,
			"repeat": [1, 1],
			"magFilter": "LinearFilter",
			"minFilter": "LinearMipMapLinearFilter",
			"encoding": "LinearEncoding"
		},

		"tex_yh3": {
			"url": "_tex/yh_03.jpg",
			"anisotropy": 16,
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
