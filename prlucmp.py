import networkx as nx
G=nx.read_edgelist("/Users/wxy/Desktop/572hw4/linkextract/edgelist.txt",create_using=nx.DiGraph())
page_ranks=nx.pagerank(G,alpha=0.85,personalization=None, max_iter=40, tol=1e-06, nstart=None,weight='weight', dangling=None)
#print(page_ranks.items())
# count=0
with open("external_pageRankFile.txt","w") as file:
	for docID,rank in page_ranks.items():
		# count+=1
		file.write("/Users/wxy/Desktop/572hw4/nytimes/"+docID+"="+str(rank)+"\n")
file.close()
# print(page_ranks)