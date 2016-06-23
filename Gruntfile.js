module.exports = function(grunt) {

    grunt.initConfig({

        jshint: {
            files: [
                'Gruntfile.js',
                'src/assets/js/src/media-management.js'
            ],
            options: {
                loopfunc: true,
                globals: {
                    jQuery: true,
                    console: true,
                    module: true,
                    document: true
                }
            }
        },
        concat: {
            options: {
                separator: ';' // If CSS, cleancss will remove it
            },
            update: {
                files: [
                {
                    src: [
                        'bower_components/jquery-file-upload/js/vendor/jquery.ui.widget.js',
                        'bower_components/jquery-file-upload/js/jquery.iframe-transport.js',
                        'bower_components/jquery-file-upload/js/jquery.fileupload.js',
                        'bower_components/photoset-grid/jquery.photoset-grid.js'
                    ],
                    dest: 'src/assets/js/src/file-upload.js'
                }
                ]
            },
            build: {
                files: [
                {
                    src: [
                        'src/assets/js/src/file-upload.js',
                        'src/assets/js/src/media-management.js'
                    ],
                    dest: 'src/assets/js/media-management.js'
                }
                ]
            }
        },
        uglify: {
            options: {
                mangle: true,
                compress: true,
                beautify: false,
            },
            files: {
                expand: true,
                cwd: 'src/assets/js',
                src: ['*.js'],
                dest: 'public/js',
                ext: '.min.js',
                flatten: true,
                filter: 'isFile',
                rename: function(base, src) {
                    return base+'/'+src.replace(/\/([^\/]*)$/, '/../$1');
                }
            }
        },
        clean : {
            src : "src/assets/js/media-management.js",
        },
        less: {
            options: {
                cleancss: true,
                compress: true
            },
            files: {
                expand: true,
                cwd: 'src/assets/less',
                src: ['*.less'],
                dest: 'public/css',
                ext: '.css',
                flatten: true,
                filter: 'isFile',
                rename: function(base, src) {
                    return base+'/'+src.replace(/\/([^\/]*)$/, '/../$1');
                }
            }
        },
        watch: {
            less: {
                files: ['src/assets/less/*.less'],
                tasks: ['less'],
            },
            js: {
                files: [
                    'src/assets/js/src/*.js',
                ],
                tasks: [
                    'jshint',
                    'concat:build',
                    'uglify',
                    'clean'
                ],
            }
        }
    });

    grunt.loadNpmTasks('grunt-contrib-jshint');
    grunt.loadNpmTasks('grunt-contrib-concat');
    grunt.loadNpmTasks('grunt-contrib-uglify');
    grunt.loadNpmTasks('grunt-contrib-clean');
    grunt.loadNpmTasks('grunt-contrib-less');
    grunt.loadNpmTasks('grunt-contrib-watch');

    grunt.registerTask('default', function() {
        grunt.task.run([
            'jshint',
            'concat:build',
            'uglify',
            'clean',
            'less'
        ]);
    });

    grunt.registerTask('update', 'Task to run after updating dependencies', function() {
        grunt.task.run([
            'concat:update'
        ]);
        grunt.task.run('default');
    });
};