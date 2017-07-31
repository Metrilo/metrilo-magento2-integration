### Installing Kubernates containers with Magento2 stuff ###

```bash
# Prepare the database
$ kubectl create -f magento2-plugin-db-pvc.yaml
$ kubectl create -f magento2-plugin-db-deployment.yaml
$ kubectl create -f magento2-plugin-db-service.yaml

# Install the web
$ kubectl create -f magento2-plugin-deployment.yaml
$ kubectl create -f magento2-plugin-service.yaml
```

1) Try to install the Magento2 via `/setup` endpoint
2) If this fails ssh to the pod and run `scripts/setup_store.sh`

Voala
