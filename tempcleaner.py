import os
import sys
import shutil
import time
import traceback
import re

try:
	os.chdir("/var/www/dev/pansophy/Pansophy/tmp")
	print "Guarding the temp directory..."
	while(1):
		filelist = os.listdir('.')
		for x in filelist:
			try:
				filenames = os.listdir('./'+x)
				for y in filenames:
					if not(re.match(".*[.].*", y)):
						timestamp = y
				if ((time.time() - int(timestamp)) > 300):
					shutil.rmtree('./'+x,True)
			except IOError:
				print "File currently being accessed"
			except:
				print "Unforeseen consequence: ", sys.exc_info()[0]
				raise
				
		time.sleep(5)
except KeyboardInterrupt:
	raise
except SystemExit:
	raise
except Exception, e:
	print "Unforeseen consequences: " + str(e)
	traceback.print_exc()
	os._exit(1)
