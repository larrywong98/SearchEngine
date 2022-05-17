# Solr PHP Client

This is a client used to retrieve data from the Solr Search Engine.<br />

Here is my demo video: https://www.youtube.com/watch?v=-15nM6ETu7A<br />

## Frontend functionalities
1. Search words in the search bar. Get results from the Solr server<br />
2. Sort by Lucene(default) or Pagerank algorithm<br />
3. Autocomplete by given characters<br />
4. Spellcorrect and guess when the result is empty<br />

## Backend workflow
1. Extract links from 16400 htmls from the new york times website<br />
2. Indexing pages with Tika<br />
3. Compute pagerank with networkx library<br />
4. Get search request from client use Lucene or alternative Pagerank method<br />
6. Compare Lucene results with Pagerank result to evaluate the overlap<br />
