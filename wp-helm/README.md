# my-wordpress Helm Chart

This Helm chart deploys a WordPress application on an OpenShift cluster. It includes the following resources:

- Deployment for the WordPress application
- Horizontal Pod Autoscaler (HPA) for scaling
- Route for external access
- Service for internal communication
- PersistentVolumeClaim (PVC) for caching

## Features

- Automatically scales pods based on CPU utilization using an HPA.
- Includes configurations for TLS-secured routes.
- Uses persistent storage for fast CGI caching.
- Supports custom overrides via the `values.yaml` file.

## Prerequisites

1. Access to an OpenShift cluster with sufficient permissions to create the required resources.
2. A storage class (`netapp-file-standard`) configured for the cluster.
3. TLS certificates for the route (private key, certificate, and CA certificate).

## Installation Instructions

1. Clone this repository or include the chart in your Helm repository.
2. Prepare a custom `values.yaml` file to override default values (see below for details on possible overrides).
3. Install the chart using the following command:

   ```bash
   helm install my-wordpress ./my-wordpress -f custom-values.yaml
   ```

4. Verify the installation:

   ```bash
   oc get all -n abc123-dev
   ```

## Overrides and Descriptions

The following table describes key values in `values.yaml` that can be overridden to customize the deployment:

| Key                             | Default Value                            | Description                                                                 |
|---------------------------------|------------------------------------------|-----------------------------------------------------------------------------|
| `namespace`                     | `abc123-dev`                             | The namespace in which to deploy the resources.                            |
| `appName`                       | `my-wordpress`                           | The name of the WordPress application.                                     |
| `imageNamespace`                | `abc123-tools`                           | Namespace of the image registry.                                           |
| `imageRegistry`                 | `image-registry.openshift-image-registry.svc:5000` | URL of the image registry.                                                 |
| `wordpress.replicaCount`        | `1`                                      | The number of WordPress pods to deploy.                                    |
| `wordpress.service.port`        | `80`                                     | Port for the WordPress service.                                            |
| `wordpress.service.targetPort`  | `8080`                                   | Target port for the WordPress container.                                   |
| `wordpress.hpa.minReplicas`     | `1`                                      | Minimum number of pods for the HPA.                                        |
| `wordpress.hpa.maxReplicas`     | `3`                                      | Maximum number of pods for the HPA.                                        |
| `wordpress.hpa.averageCpuUtilization` | `80`                               | CPU utilization threshold for scaling.                                     |
| `wordpress.resources.requests.cpu` | `100m`                                | CPU request for the WordPress container.                                   |
| `wordpress.resources.requests.memory` | `128Mi`                            | Memory request for the WordPress container.                                |
| `wordpress.resources.limits.cpu` | `100m`                                 | CPU limit for the WordPress container.                                     |
| `wordpress.resources.limits.memory` | `256Mi`                              | Memory limit for the WordPress container.                                  |
| `wordpress.whitelistedIPs`      | `*`                                      | Comma-separated list of IPs allowed to access the route.                   |
| `wordpress.php.debug.enabled`   | `false`                                  | Enable or disable PHP debugging.                                           |
| `domain`                        | `dev.mydomain.com`                       | The domain for the route.                                                  |
| `serviceAccountName`            | `abc123-vault`                           | The name of the service account used by the application.                   |

## Additional Elements

To use this chart, ensure you include the following elements:

1. **TLS Certificates**: The chart includes stub files in the `certs/` folder. Please reference the .github/workflows/helm_upgrade.yaml file for examples on how overwrite these files during your helm upgrade with the secrets from Github Secrets which will allow you to deploy the cert while keeping your secrets secure. In your helm pipeline place your private key, certificate, and CA certificate in the `certs/` directory as follows:
   - `private.key`: Private key file.
   - `certificate.crt`: Certificate file.
   - `cacertificate.crt`: CA certificate file.

2. **Custom Secrets**:
   - A secret named `composer-auth` containing a key `auth.json` for Composer authentication.
   - A secret matching the `appName` containing environment variables for the application. A template secret is included in the chart that provides a starting point for this. It contains the following:

| Key                             | Default Value        | Description                                                                 |
|---------------------------------|----------------------|-----------------------------------------------------------------------------|
| `WORDPRESS_DATABASE_HOST`       | N/A                  | The name of the service that points to the MySQL/MariaDB stateful set       |
| `WORDPRESS_DATABASE_NAME`       | N/A                  | The name of the database hosed in MySQL/MariaDB service                     |
| `WORDPRESS_DATABASE_PORT_NUMBER`| 3306                 | The post your MySQL/MariaDB service is hosted on                            |
| `WORDPRESS_DATABASE_USER`       | N/A                  | The MySQL/MariaDB account username (please don't use root)                  |
| `WORDPRESS_DATABASE_PASSWORD`   | N/A                  | The MySQL/MariaDB account password                                          |
| `WORDPRESS_TABLE_PREFIX`        | N/A                  | If there's a prefix on the DB tables it can be added here.                  |
| `WORDPRESS_USERNAME`            | N/A                  | The wordpress admin username for /wp-admin                                  |
| `WORDPRESS_PASSWORD`            | N/A                  | The wordpress admin user account password                                   |
| `OVERRIDE_HEALTH`               | NULL                 | Strictly speaking this is for Rapunzel and should be moved.                 |
| `WHO_AM_I`                      | GOLD                 | Strictly speaking this is for Rapunzel and should be moved.                 |

1. **Persistent Storage**: Ensure the `netapp-file-standard` storage class is available in your cluster for the PVC.

## Accessing the Application

Once deployed, access the application using the provided route. For example:

```bash
oc get route my-wordpress -n abc123-dev
```

This command will return the external URL for your WordPress application.

## Uninstallation

To uninstall the chart and delete all associated resources:

```bash
helm uninstall my-wordpress -n abc123-dev
```

## Support

For any issues or questions, please contact the chart maintainer or open an issue in the repository.
