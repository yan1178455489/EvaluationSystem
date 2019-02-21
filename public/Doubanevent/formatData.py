
def createEventsFile():
    f = open('events.csv','w')
    f2 = open('events_cand.csv')
    f3 = open('events_train.csv')
    for line in f2:
        f.write(line)
    for line in f3:
        f.write(line)
    f.close()
    f2.close()
    f3.close()

def writeEventMap():
    eset = set()
    f = open('events.csv')
    for line in f:
        s = line.strip().split(',')
        eset.add(s[0])
    f.close()
    f = open('event_map.csv','w')
    count = 0
    for e in eset:
        f.write(str(count)+','+e+'\n')
        count += 1
    f.close()

def readMap(file,c=1):
    f = open(file)
    m = dict()
    if c ==1:
        for line in f:
            s = line.strip().split(',')
            m[int(s[1])] = int(s[0])
        f.close()
    else:
        for line in f:
            s = line.strip().split(',')
            m[int(s[0])] = int(s[1])
    return m

##writeEventMap()
##createEventsFile()
##umap = readMap('E:/dataset/DoubanEvent/GroupConcert/beijing/candevents_map.csv')
##f = open('org_map.csv','w')
##for oid,nid in umap.items():
##    f.write(str(nid-1)+','+str(oid)+'\n')
##f.close()

##umap = readMap('E:/dataset/DoubanEvent/GroupConcert/beijing/user_map.csv',0)
##orgmap = readMap('E:/dataset/DoubanEvent/GroupConcert/beijing/org_map.csv',0)
##f = open('userorg.csv','w')
##f2 = open('host_followers.csv')
##for line in f2:
##    s = line.strip().split(',')
##    f.write(str(umap[int(s[0])])+','+str(orgmap[int(s[1])])+'\n')
##f2.close()
##f.close()

f = open('event_map.csv')
events = set()
for line in f:
    s = line.strip().split(',')
    events.add(s[1])
f.close()

print len(events)
    
f = open('event_map.csv','w')
for i,e in enumerate(events):
    f.write(str(i)+','+e+'\n')
f.close()
