module.exports = function(grunt) {

    grunt.initConfig({
        pkg: grunt.file.readJSON('package.json'),

        sass: {
            dist: {
                files: {
                    'ext/css/build/main.css': 'ext/css/main.scss',
                }
            }
        },

        autoprefixer: {
            options: {
                browsers: ['> 0.5%', 'last 2 versions', 'Firefox ESR', 'Opera 12.1', 'Android 4', 'BlackBerry 10']
            },
            styles: {
                src: 'ext/css/build/*.css'
            }
        },

        cssmin: {
            combine: {
                files: {
                    'ext/css/build/style.min.css': [
                        'node_modules/c3/c3.css',
                        'ext/css/build/main.css',
                    ]
                }
            }
        },

        clean: [
            'ext/css/build/main.css',
        ],

        jshint: {
            options: {
                reporter: './jshint-reporter.js',
                browser: true, // We're a browser ...
                devel: true, // ... that has development features \o/
                strict: true, // Enforce strict mode for all functions
                curly: true, // Require curly brackets for blocks
                forin: true, // Require for-in loop to filter items
                latedef: true, // Prevent variables being used before they are defined
                undef: true, // Prevent the use of undefined variables
                unused: 'vars', // Highlight unused variables (vars excludes function params)
                globals: { // We made these global on purpose - false is read-only
                    selectedInterface: false,
                    c3: false
                }
            },
            all: ['ext/jsc/*.js']
        },

        uglify: {
            options: {
                preserveComments: 'some',
                sourceMap: true,
                sourceMapIncludeSources: true
            },
            target: {
                files: {
                    'ext/jsc/build/script.min.js': [
                        'node_modules/d3/d3.js',
                        'node_modules/c3/c3.js',
                        'ext/jsc/main.js'
                    ]
                }
            }
        },

        svgstore: {
            images: {
                files: {
                    'ext/img/build/images.svg': ['ext/img/*.svg']
                }
            },
        },

        watch: {
            styles: {
                files: ['ext/css/*.css', 'ext/css/*.scss'],
                tasks: ['style'],
            },
            scripts: {
                files: ['ext/jsc/*.js'],
                tasks: ['script']
            }
        }
    });

    grunt.loadNpmTasks('grunt-contrib-sass');
    grunt.loadNpmTasks('grunt-autoprefixer');
    grunt.loadNpmTasks('grunt-contrib-cssmin');

    grunt.loadNpmTasks('grunt-contrib-jshint');
    grunt.loadNpmTasks('grunt-contrib-uglify');

    grunt.loadNpmTasks('grunt-svgstore');

    grunt.loadNpmTasks('grunt-contrib-clean');
    grunt.loadNpmTasks('grunt-contrib-watch');

    grunt.registerTask('style', ['sass', 'autoprefixer', 'cssmin', 'clean']);
    grunt.registerTask('script', ['jshint', 'uglify']);
    grunt.registerTask('image', ['svgstore']);

    grunt.registerTask('default', ['style', 'script', 'image']);

};
