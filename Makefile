build:
	echo "USERNAME: $MAGE_DEV_UNAME"
	echo "PASSWORD: $MAGE_DEV_PASSWORD"
	./scripts/build.sh
deploy:
	./scripts/deploy.sh
