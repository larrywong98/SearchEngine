with open("output1.txt",'r') as file:
    count=1
    indicator=0
    lucene=[]
    pagerank=[]
    # count1=1
    for record in file:
        # print(indicator,end=" ")
        if indicator==1:
            print(record[:-1])
        
        if indicator==0:
            lucene.append(record[:-1])
        if indicator==1:
            pagerank.append(record[:-1])

        if count%10==0:
            indicator=indicator^1
            print("\n")
        
        
            
        # print(lucene)
        # print(pagerank)
        # print(indicator)
        # print(record[:-1])
        count+=1

file.close()
# print(len(lucene))
# print(len(pagerank))

count=[0 for i in range(len(lucene)//10)]
for i in range(len(lucene)//10):
    for j in range(i*10,i*10+9):
        # print(lucene[j])
        # count+=1 
        for p in range(j+1,i*10+10):
            if lucene[j]==pagerank[p]:
                count[i]+=1
                # print(lucene[j])
                # print(pagerank[p])
print(count)