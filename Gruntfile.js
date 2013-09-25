'use strict';

module.exports = function(grunt) {

  // Project configuration.
  grunt.initConfig({
    imagemin: {
      theme: {
        files: [
          {
            expand: true,
            cwd: "wp-content/themes/designfolio-pro/",
            src: "**/*.{png,jpg}",
            dest: "wp-content/themes/designfolio-pro/"
          }
        ]
      },
      plugins: {
        files: [
          {
            expand: true,
            cwd: "wp-content/plugins",
            src: "**/*.{png,jpg}",
            dest: "wp-content/plugins"
          }
        ]
      }
    }
  });

  // These plugins provide necessary tasks.
  grunt.loadNpmTasks('grunt-contrib-imagemin');
  grunt.registerTask('default', ['imagemin']);

};
