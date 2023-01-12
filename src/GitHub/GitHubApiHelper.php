<?php

namespace App\GitHub;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class GitHubApiHelper
{
    public function __construct(
        private HttpClientInterface $httpClient,
        private array $githubOrganizations = []
    )
    {
    }

    public function getOrganizationInfo(string $organization): GitHubOrganization
    {
        // optimization in case getOrganizationRepositories is called first
        if (isset($this->githubOrganizations[$organization])) {
            return $this->githubOrganizations[$organization];
        }

        $response = $this->httpClient->request('GET', 'https://api.github.com/orgs/' . $organization . '?per_page=200&sort=updated&type=all');

        $data = $response->toArray();

        return $this->githubOrganizations[$data['name']] = new GitHubOrganization(
            $data['name'],
            $data['public_repos']
        );
    }

    /**
     * @return GitHubRepository[]
     */
    public function getOrganizationRepositories(string $organization): array
    {
        $response = $this->httpClient->request('GET', sprintf('https://api.github.com/orgs/%s/repos?per_page=200&sort=updated&type=all', $organization));

        $data = $response->toArray();

        $repositories = [];
        $publicRepoCount = 0;
        foreach ($data as $repoData) {
            $repositories[] = new GitHubRepository(
                $repoData['name'],
                $repoData['html_url'],
                \DateTimeImmutable::createFromFormat('Y-m-d\TH:i:s\Z', $repoData['updated_at'])
            );

            if ($repoData['private'] === false) {
                ++$publicRepoCount;
            }
        }

        if (!isset($this->githubOrganizations[$organization])) {
            $this->githubOrganizations[$organization] = new GitHubOrganization(
                $data[0]['owner']['login'],
                $publicRepoCount
            );
        }

        return $repositories;
    }

}
