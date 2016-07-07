using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.Threading.Tasks;
using Famoser.FrameworkEssentials.Logging;
using Famoser.FrameworkEssentials.Logging.Interfaces;

namespace Famoser.Telemetry.UniversalWindows.Logger
{
    public class TelemetryLogger : ILogger
    {
        public TelemetryLogger(string url, string applicationId)
        {
            
        }

        public void AddLog(LogModel model)
        {
            throw new NotImplementedException();
        }

        public void ClearLogs()
        {
            throw new NotImplementedException();
        }

        public List<LogModel> GetLogs(bool clearAfterwards = true)
        {
            throw new NotImplementedException();
        }
    }
}
