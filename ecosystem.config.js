module.exports = {
  apps: [{
    name: 'vite-dev',
    script: 'npm',
    args: 'run dev',
    cwd: './',
    watch: false,
    env: {
      NODE_ENV: 'development'
    }
  }]
}