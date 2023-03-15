<?php
// Fork of https://gist.github.com/mtwalsh/fce3c4aa416996e5900e8ac9f471dd6c, thanks!
// TODO: use full automated approach later https://t3terminal.com/blog/typo3-gitlab-deployment/
// TODO: use vite

namespace Deployer;

require 'recipe/common.php';

// Project name
set('application', 'new.egoevchargers.com');

// Project repository
// See: https://blog.harveydelaney.com/configuring-multiple-deploy-keys-on-github-for-your-vps/
set('repository', 'paddyphilipryan/egoev.git');
// If you have only one deploy key, just use 
// set('repository', 'git@githosting.com:enovatedesign/project.git');

// Shared files/dirs between deploys
set('shared_files', [
    '.env'
]);
set('shared_dirs', [
    'storage'
]);

// Writable dirs by web server
set('writable_dirs', [
    'storage',
    'storage/runtime',
    'storage/logs',
    'storage/rebrand',
    'public/cpresources',
    // added by myself
    'public/media-files'
]);

// TODO: do we need it?
// Set the worker process user
// set('http_user', 'worker');

// Set the default deploy environment to production
set('default_stage', 'production');

// TODO: is this needed?
// Disable multiplexing
set('ssh_multiplexing', false);

// Tasks

// TODO: add this later
// Upload build assets
task('upload', function () {
    upload(__DIR__ . "/public/assets/", '{{release_path}}/public/assets/');
    //upload(__DIR__ . "/public/service-worker.js", '{{release_path}}/public/service-worker.js');
});

// TODO: this is in tasks?
desc('Execute migrations');
task('craft:migrate', function () {
    // TODO: Steps from https://github.com/nystudio107/devmode/blob/develop/buddy.yml#L94
    run('{{release_path}}/craft off --retry=60');
    // - "# Backup the database just in case any migrations or Project Config changes have issues"
    // - "php craft backup/db" ?
    // - "# Run pending migrations, sync project config, and clear caches"
    run('{{release_path}}/craft clear-caches/all');
    run('{{release_path}}/craft migrate/all');
    // originally: run('{{release_path}}/craft up');
    // - "# Turn Craft on"
    run('{{release_path}}/craft on');
})->once();

// Hosts

// Production Server(s)

host('production')
    ->set('remote_user', 'ploi')
    ->set('hostname', '116.202.111.32')
    ->set('deploy_path', '~/new.egoevchargers.com');

/*host('110.164.16.59', '110.164.16.34', '110.164.16.50')
    ->set('deploy_path', '/websites/{{application}}')
    ->set('branch', 'master')
    ->stage('production')
    ->user('someuser');

// Staging Server
host('192.168.16.59')
    ->set('deploy_path', '/websites/{{application}}')
    ->set('branch', 'develop')
    ->stage('staging')
    ->user('someuser');
*/
// Group tasks

desc('Deploy your project');
task('deploy', [
    // 'deploy:info', --> THIS IS INCLUDED IN PREPARE in v7
    'deploy:prepare',
    // 'deploy:lock', --> THIS IS INCLUDED IN PREPARE in v7
    //'deploy:release', --> THIS IS INCLUDED IN PREPARE in v7
    // 'deploy:update_code', --> THIS IS INCLUDED IN PREPARE in v7

    // TODO: re-add later
    // 'upload', // Custom task to upload build assets

    // 'deploy:shared', -> --> THIS IS INCLUDED IN PREPARE in v7
    // 'deploy:writable', -> --> THIS IS INCLUDED IN PREPARE in v7
    'deploy:vendors',
    'deploy:clear_paths',
    // 'deploy:symlink',  --> THIS IS INCLUDED in publish
    // 'deploy:unlock', --> THIS IS INCLUDED in publish
    'deploy:publish',
    // 'deploy:cleanup',  --> THIS IS INCLUDED in publish
    // 'deploy:success' --> THIS IS INCLUDED in publish
]);

// [Optional] Run migrations
after('deploy:vendors', 'craft:migrate');

// [Optional] If deploy fails automatically unlock
after('deploy:failed', 'deploy:unlock');

// Run with '--parallel'
// dep deploy --parallel
