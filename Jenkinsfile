pipeline {
    agent any

    environment {
        IMAGE_NAME     = "todo-app"
        CONTAINER_NAME = "Todo-app"
        APP_PORT       = "8080"
        COMPOSE_FILE   = "/home/ubuntu/php-app/docker-compose.yml"
    }

    stages {

        // ── Stage 1: Checkout ──────────────────────────────────────────
        stage('Checkout') {
            steps {
                echo '📥 Pulling latest code from GitHub...'
                checkout scm
                sh 'ls -la'
            }
        }

        // ── Stage 2: Inject Secrets ────────────────────────────────────
        stage('Inject Secrets') {
            steps {
                echo '🔐 Creating .env from Jenkins credentials...'
                withCredentials([
                    string(credentialsId: 'DB_HOST', variable: 'DB_HOST'),
                    string(credentialsId: 'DB_USER', variable: 'DB_USER'),
                    string(credentialsId: 'DB_PASS', variable: 'DB_PASS'),
                    string(credentialsId: 'DB_NAME', variable: 'DB_NAME')
                ]) {
                    sh '''
                        echo "DB_HOST=${DB_HOST}" >  .env
                        echo "DB_USER=${DB_USER}" >> .env
                        echo "DB_PASS=${DB_PASS}" >> .env
                        echo "DB_NAME=${DB_NAME}" >> .env
                        echo "DB_PORT=3306"       >> .env
                        echo "✅ .env file created"
                        # Copy to app directory
                        cp .env /home/ubuntu/php-app/.env
                    '''
                }
            }
        }

        // ── Stage 3: Build Docker Image ────────────────────────────────
        stage('Build') {
            steps {
                echo '🐳 Building Docker image...'
                sh '''
                    cd /home/ubuntu/php-app
                    docker build -t ${IMAGE_NAME}:${BUILD_NUMBER} .
                    docker tag  ${IMAGE_NAME}:${BUILD_NUMBER} ${IMAGE_NAME}:latest
                    echo "✅ Built ${IMAGE_NAME}:${BUILD_NUMBER}"
                '''
            }
        }

        // ── Stage 4: Test ──────────────────────────────────────────────
        stage('Test') {
            steps {
                echo '🧪 Running tests...'
                withCredentials([
                    string(credentialsId: 'DB_HOST', variable: 'DB_HOST'),
                    string(credentialsId: 'DB_USER', variable: 'DB_USER'),
                    string(credentialsId: 'DB_PASS', variable: 'DB_PASS'),
                    string(credentialsId: 'DB_NAME', variable: 'DB_NAME')
                ]) {
                    sh '''
                        docker run --rm \
                            -e DB_HOST=${DB_HOST} \
                            -e DB_USER=${DB_USER} \
                            -e DB_PASS=${DB_PASS} \
                            -e DB_NAME=${DB_NAME} \
                            -e DB_PORT=3306 \
                            ${IMAGE_NAME}:latest \
                            php /var/www/html/tests/run_tests.php
                    '''
                }
            }
        }

        // ── Stage 5: Deploy ────────────────────────────────────────────
        stage('Deploy') {
            steps {
                echo '🚀 Deploying new container...'
                sh '''
                    cd /home/ubuntu/php-app

                    # Stop and remove old container
                    docker stop ${CONTAINER_NAME} 2>/dev/null || true
                    docker rm   ${CONTAINER_NAME} 2>/dev/null || true

                    # Start fresh container
                    docker-compose up -d --build

                    echo "✅ Container deployed"
                '''
            }
        }

        // ── Stage 6: Health Check ──────────────────────────────────────
        stage('Health Check') {
            steps {
                echo '❤️  Checking app health...'
                sh '''
                    sleep 5
                    STATUS=$(curl -s -o /dev/null -w "%{http_code}" \
                        http://localhost:${APP_PORT}/tasks.php)

                    if [ "$STATUS" = "200" ]; then
                        echo "✅ App healthy — HTTP $STATUS"
                    else
                        echo "❌ Health check failed — HTTP $STATUS"
                        docker logs ${CONTAINER_NAME} --tail=20
                        exit 1
                    fi
                '''
            }
        }

        // ── Stage 7: Cleanup ───────────────────────────────────────────
        stage('Cleanup') {
            steps {
                echo '🧹 Removing old images...'
                sh '''
                    docker image prune -f
                    docker images ${IMAGE_NAME} --format "{{.Tag}}" | \
                        grep -v latest | sort -rn | tail -n +4 | \
                        xargs -r -I {} docker rmi ${IMAGE_NAME}:{} 2>/dev/null || true
                    echo "✅ Cleanup done"
                '''
            }
        }
    }

    post {
        success {
            echo '''
            ╔══════════════════════════════════╗
            ║  ✅ PIPELINE SUCCESS              ║
            ║  App live on port 8080            ║
            ╚══════════════════════════════════╝
            '''
        }
        failure {
            echo '''
            ╔══════════════════════════════════╗
            ║  ❌ PIPELINE FAILED               ║
            ║  Check logs above                 ║
            ╚══════════════════════════════════╝
            '''
            sh 'docker logs ${CONTAINER_NAME} --tail=30 2>/dev/null || true'
        }
        always {
            cleanWs()
        }
    }
}
