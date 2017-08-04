WEB_POD=$(shell kubectl get pods | grep magento2-testenv-web | tail -n 1 | awk '{print $$1}')

build:
	./scripts/build.sh
deploy:
	./scripts/deploy.sh
update_plugin:
	kubectl exec -it $(WEB_POD) -- source /update_plugin.sh
