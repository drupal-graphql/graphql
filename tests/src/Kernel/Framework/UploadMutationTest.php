<?php

namespace Drupal\Tests\graphql\Kernel\Framework;


use Drupal\Tests\graphql\Kernel\GraphQLTestBase;
use Symfony\Component\HttpFoundation\Request;
use Drupal\graphql\GraphQL\ResolverBuilder;

/**
 * Test file uploads with graphql.
 *
 * @group graphql
 */
class UploadMutationTest extends GraphQLTestBase {

  /**
   * Test a simple file upload.
   */
  public function testFileUpload() {
    $gql_schema = <<<GQL
      schema {
        mutation: Mutation
      }
      type Mutation {
        store(file: Upload!): String
      }

      scalar Upload
GQL;

    $this->setUpSchema($gql_schema, $this->getDefaultSchema());
    $builder = new ResolverBuilder();

    // Create dummy file, since symfony will test if it exists..
    $file = file_directory_temp() . '/graphql_upload_test.txt';
    touch($file);

    // Mock a mutation that accepts the upload input and just returns
    // the client filename.
    $this->mockField('store', [
      'name' => 'store',
      'type' => 'String',
      'parent' => 'Mutation',
    ], function ($value, $args, $context, $info) {
        $file = $args['file'];
        return $file->getClientOriginalName();
      }
    );

    // Create a post request with file contents.
    $uploadRequest = Request::create('/graphql/graphql_test', 'POST', [
      'query' => 'mutation($upload: Upload!) { store(file: $upload) }',
      // The variable has to be declared null.
      'variables' => ['upload' => NULL],
      // Then map the file upload name to the variable.
      'map' => [
        'file' => ['variables.upload'],
      ],
    ], [], [
      'file' => [
        'name' => 'test.txt',
        'type' => 'text/plain',
        'size' => 42,
        'tmp_name' => $file,
        'error' => UPLOAD_ERR_OK,
      ],
    ]);

    $uploadRequest->headers->add(['content-type' => 'multipart/form-data']);
    $response = $this->container->get('http_kernel')->handle($uploadRequest);
    $result = json_decode($response->getContent());
    $this->assertEquals('test.txt', $result->data->store);
  }
}
